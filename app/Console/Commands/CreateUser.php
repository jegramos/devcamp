<?php

namespace App\Console\Commands;

use App\Actions\User\CreateUserAction;
use App\Models\User;
use App\Rules\DbVarcharMaxLengthRule;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\form;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class CreateUser extends Command
{
    protected $signature = 'user:create
                           {--I|interactive : Run in interactive mode}
                           {--G|given-name= : Given name of the user. E.g.: --given-name="Juan"}
                           {--F|family-name= : Family name of the user. E.g.: --family-name="Dela Cruz"}
                           {--E|email= : Email of the user. E.g.: --email="sample@example.com"}
                           {--U|username= : Username of the user. E.g.: --username="juan.delacruz"}
                           {--P|password= : Password of the user. E.g.: --password="Test_passw0rd"}
                           {--R|roles=* : Roles of the user. E.g.: --roles="admin" --roles="user"}';

    protected $description = 'Create a new user';

    public function handle(CreateUserAction $createUserAction): int
    {
        if ($this->option('interactive')) {
            $data = $this->handleInteractivePrompts();
        }

        if (! $this->option('interactive')) {
            try {
                $data = $this->handleCommandLineInput();
            } catch (ValidationException $e) {
                error($e->getMessage());

                return Command::FAILURE;
            }
        }

        // Assume the email is valid since it was created via the console
        $data['email_verified_at'] = Carbon::now();

        try {
            $user = $createUserAction->execute($data);
        } catch (Throwable $th) {
            $this->error('Error: ' . $th->getMessage());

            return Command::FAILURE;
        }

        $this->displayUserDetails($user);

        return Command::SUCCESS;
    }

    /**
     * Handle user input via interactive prompts.
     * Prompts the user for required fields and validates the input.
     */
    private function handleInteractivePrompts(): array
    {
        return form()
            ->text(
                'Given Name',
                validate: ['required', new DbVarcharMaxLengthRule()],
                name: 'given_name',
            )
            ->text(
                'Family Name',
                validate: ['required', new DbVarcharMaxLengthRule()],
                name: 'family_name',
            )
            ->text(
                'Email',
                validate: ['required', 'email:rfc', 'unique:users,email'],
                name: 'email',
                transform: fn ($email) => strtolower($email),
            )
            ->text(
                'Username',
                validate: ['required', new UsernameRule()],
                name: 'username',
                transform: fn ($email) => strtolower($email),
            )
            ->password(
                'Password',
                validate: ['required', new PasswordRule()],
                name: 'password',
            )
            ->multiselect(
                'Roles',
                options: $this->getAllRoles(),
                required: true,
                name: 'roles'
            )
            ->submit();
    }

    /**
     * Handle user input through command-line options.
     * Validates the input data from the command-line options.
     *
     * @throws ValidationException
     */
    private function handleCommandLineInput(): array
    {
        $data = [
            'given_name' => $this->option('given-name'),
            'family_name' => $this->option('family-name'),
            'email' => $this->option('email'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
            'roles' => $this->option('roles'),
        ];

        Validator::validate($data, [
            'given_name' => ['required', new DbVarcharMaxLengthRule()],
            'family_name' => ['required', new DbVarcharMaxLengthRule()],
            'email' => ['required', 'email:rfc', 'unique:users,email'],
            'username' => ['required', new UsernameRule()],
            'password' => ['required', new PasswordRule()],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'string', 'distinct', 'exists:roles,name'],
        ]);

        return $data;
    }

    /**
     * Retrieve all roles from the database.
     */
    private function getAllRoles(): array
    {
        return Role::all()->pluck('name')->toArray();
    }

    /**
     * Display the details of the newly created user.
     */
    private function displayUserDetails(User $user): void
    {
        info('The following user has been created:');
        $roles = $user->getRoleNames()->implode(', ');
        table(
            headers: ['ID', 'Email', 'Username', 'Name', 'Roles'],
            rows: [[$user->id, $user->email, $user->username, $user->userProfile->full_name, $roles]],
        );
    }
}
