## About DevCamp

> **NOTE:**  This project is a work-in-progress and is currently running on AWS free-tier. You may experience some small
> bugs and performance issues.
> [See DevCamp Live](https://app.devcamp.site)
> or [Visit a portfolio built on DevCamp](https://jegramos.works.devcamp.site)

A convenient open-source destination for <i>building</i> and <i>showcasing</i> portfolios. With DevCamp you can:

- Build beautiful portfolio websites with your free subdomain in just a few minutes
- Calendar booking system with your clients (WIP)
- Write and share your blog posts (WIP)

⠀
> **CONTRIBUTE:**  If you find this project interesting and worth your time, you may submit a PR.

## Set up your local development environment

#### It is recommended to use [Laravel Herd](https://herd.laravel.com/), but you may follow these steps for a manual installation

- Minimum of PHP 8.2 installed with a database engine that supports JSON types and possibly with full text search (e.g.
  MySQL8, MariaDB 10.5)
- Create a **.env** and a **.env.testing** files from the **.env.example** that came with this project. You may request
  some of the contents of these files from me
- Locate your **php.ini** file and change the value **upload_max_filesize** to **10M**. See
  this [guide](https://devanswers.co/ubuntu-php-php-ini-configuration-file/) if you're having trouble finding the
  directory of your php.ini file
- Make sure you have MySQL and Redis running locally (or depending on what's stated in the **.env** file)
- Run the command `composer install`  to install all the project and dev dependencies
- Then run `php artisan migrate --seed && php artisan app:format-code`
- To check if everything is working as expected, run: `php artisan test`

#### You may also use `Docker` to build the application and databases

```
docker compose up --profile app --profile store up -d
```

## Tools ready for you

Runs a [code styler](https://laravel.com/docs/11.x/pint) for consistency and
generate [IDE helper PHP Docs](https://github.com/barryvdh/laravel-ide-helper). See the command at
`app/Console/Commands/FormatCodeCommand.php`

```
php artisan app:format-code
```

\
Create a user with role. See the command at `app/Console/Commands/CreateUserCommand.php`

```
php artisan user:create -I
```

\
Running `git commit` will trigger automated tasks specified in `grumphp.yml`

- PSR-compliant code formatting
- Package security checks
- Unit and feature tests

\
[Laravel Horizon](https://laravel.com/docs/11.x/horizon) and [Telescope](https://laravel.com/docs/11.x/telescope) are
installed in this project

## Manually serve the app in your local environment

- Terminal 1: Run `php artisan serve`
- Terminal 2: Run `php artisan horizon`

## Project / Feature Goals

- [x] Login & Registration (H)
- [x] Login via Social Accounts (H)
- [x] Passkey Authentication (H)
- [x] User Management (H)
- [x] Profile Management (H)
- [x] CMS Dynamic Theme (L)
- [x] Resume Builder (H)
- [x] Subdomain Routing (M)
- [x] Integration of InertiaForm & Vuelidate (M)
- [x] GrumPHP checks for local development (M)
- [ ] Calendar System (M)
- [ ] Blogs Feature (M)
- [ ] Custom Domain Routing
- [ ] CI/CD with Github Actions (L)
- [ ] Add 2 more Portfolio Themes (M)
- [ ] Revamp Landing Page (L)
- [ ] MFA (L)
- [ ] Set-up Reverb for realtime notifications (L)
- [ ] UI Automation with Laravel Dusk (L)

## Tech Stack & Tools

- Laravel 11 (PHP 8.2)
- VueJS 3 (TypeScript)
- InertiaV2
- TailwindCSS and PrimeVue
- MySQL
- Redis
- Docker
- AWS (S3, EC2, ALB, Route53, etc.)
