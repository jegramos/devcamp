<?php

use App\Actions\CreateApiErrorResponseAction;
use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Inertia\Inertia;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Use redis for throttling requests
        $middleware->throttleWithRedis();

        // Set-up Inertia
        $middleware->web(append: [
            HandleInertiaRequests::class
        ]);

        $middleware->redirectGuestsTo(fn () => route('auth.login.showForm'));
        $middleware->redirectUsersTo(fn () => route('builder.resume.index'));

        $middleware->trustProxies(at: [
            '172.31.16.0/20',
            '172.31.32.0/20',
        ]);

        $middleware->trustProxies(
            headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withSchedule(function (Schedule $schedule) {
        /**
         * Delete expired password reset tokens
         * @see https://laravel.com/docs/11.x/passwords#requesting-the-password-reset-link
         */
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            // Format API (JSON) error responses to make them consistent
            if ($request->is('api/*')) {
                $createApiErrorResponse = resolve(CreateApiErrorResponseAction::class);
                return $createApiErrorResponse->execute($e);
            }

            // Handle RateLimit (429) Errors. Redirect back to the previous route with an Error Code
            if ($e instanceof ThrottleRequestsException) {
                return redirect()
                    ->back()
                    ->withHeaders($e->getHeaders())
                    ->withErrors([
                        ErrorCode::TOO_MANY_REQUESTS->value => $e->getMessage()
                    ]);
            }

            // An InvalidStateException from Socialite is likely an unauthorized request
            if ($e instanceof InvalidStateException) {
                Log::warning('Socialite: ' . $e::class, [
                    'request_url' => $request->url(),
                    'user_agent' => $request->userAgent(),
                ]);

                $response->setStatusCode(Response::HTTP_FORBIDDEN);
            }

            // Handle 404 status on portfolio subdomain (Ex: jegramos.works.devcamp.site)
            $cmsDomain = parse_url(Config::get('app.url'), PHP_URL_HOST);
            if ($request->getHost() !== $cmsDomain) {
                return redirect()->back();
            }

            // Handle auth expiration
            if ($e instanceof AuthenticationException) {
                return redirect(route('auth.login.showForm'));
            }

            // Handle page expiration or CSRF token error
            if ($response->getStatusCode() === 419) {
                return back()->with([
                    SessionFlashKey::CMS_ERROR->value => 'The page expired, please try again . ',
                ]);
            }

            /**
             * Only show Inertia modal errors during local development and testing
             * @see https://v2.inertiajs.com/error-handling
             */
            if (!app()->environment(['local', 'testing'])) {
                // Log Server Errors
                if ($response->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                    Log::error('Server Error: ' . $e::class, [
                        'error_message' => $e->getMessage(),
                        'stack_trace' => $e->getTraceAsString(),
                    ]);
                }

                $status = $response->getStatusCode();
                $message = $status === 404 ? "The page you're looking for could not be found." : $e->getMessage();
                return Inertia::render('ErrorPage', ['status' => $status, 'message' => $message])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })
    ->create();
