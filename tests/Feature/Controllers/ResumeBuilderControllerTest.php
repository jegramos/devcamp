<?php

use App\Enums\SessionFlashKey;
use App\Models\Resume;
use App\Models\ResumeTheme;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\ResumeFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNull;

beforeEach(function () {
    artisan('db:seed');
});

it('can save subdomain (only letters, numbers, and dashes allowed)', function (string $subdomain, bool $valid) {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    if ($valid) {
        followingRedirects()
            ->from(route('builder.resume.index'))
            ->post(route('builder.resume.storeSubdomain'), ['subdomain' => $subdomain])
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Builder/Resume/ResumePage')
                    ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
            );

        $user->refresh();
        assertEquals($subdomain, $user->subdomain);
        return;
    }

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeSubdomain'), ['subdomain' => $subdomain])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->has('errors.subdomain')
        );

    $user->refresh();
    assertNotEquals($subdomain, $user->subdomain);
})->with([
    'with dash' => ['subdomain' => 'new-domain', 'valid' => true],
    'with number' => ['subdomain' => 'new01domain', 'valid' => true],
    'with special characters' => ['subdomain' => 'test-domain-!test', 'valid' => false],
    'with extra dots' => ['subdomain' => 'juan.delacruz', 'valid' => false],
]);

it('checks if the subdomain is unique', function () {
    $existingSubdomain = 'test-domain';
    UserFactory::new()
        ->withSubdomain($existingSubdomain)
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeSubdomain'), ['subdomain' => $existingSubdomain])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->has('errors.subdomain')
        );

    $user->refresh();
    assertNull($user->subdomain);
});

it('can store the before the fold resume content', function () {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    $payload = [
        'name' => fake()->name(),
        'titles' => [fake()->jobTitle(), fake()->jobTitle(), fake()->jobTitle()],
        'experiences' => [fake()->unique()->sentence(3), fake()->unique()->sentence(3)],
        'socials' => [
            ['name' => 'Facebook', 'url' => 'https://facebook.com'],
            ['name' => 'LinkedIn', 'url' => 'https://linkedin.com'],
        ],
        'theme_id' => ResumeTheme::first()->id,
        'contact' => ['show' => true, 'availability_status' => fake()->sentence()]
    ];

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeContent'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    /** @var Resume $resume */
    $resume = $user->resume;

    expect($resume->name)->toBe($payload['name'])
        ->and($resume->titles)->toBe($payload['titles'])
        ->and($resume->experiences)->toBe($payload['experiences'])
        ->and($resume->socials)->toEqual($payload['socials'])
        ->and($resume->contact['show'])->toBe($payload['contact']['show'])
        ->and($resume->contact['availability_status'])->toBe($payload['contact']['availability_status']);
});

it('can store tech expertise', function () {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    $payload = [
        'name' => fake()->name(),
        'titles' => [fake()->jobTitle(), fake()->jobTitle(), fake()->jobTitle()],
        'experiences' => [fake()->unique()->sentence(3), fake()->unique()->sentence(3)],
        'socials' => [
            ['name' => 'Facebook', 'url' => 'https://facebook.com'],
            ['name' => 'LinkedIn', 'url' => 'https://linkedin.com'],
        ],
        'theme_id' => ResumeTheme::first()->id,
        'contact' => ['show' => true, 'availability_status' => fake()->sentence()],
        'tech_expertise' => [
            [
                'proficiency' => fake()->randomElement(['active', 'familiar']),
                'name' => fake()->unique()->word(),
                'description' => fake()->sentence(3),
                'logo' => 'https://google.com'
            ],
            [
                'proficiency' => fake()->randomElement(['active', 'familiar']),
                'name' => fake()->unique()->word(),
                'description' => fake()->sentence(3),
                'logo' => 'https://google.com'
            ]
        ]
    ];

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeContent'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    /** @var Resume $resume */
    $resume = $user->resume;

    expect($resume->tech_expertise)->toEqual($payload['tech_expertise']);
});

it('can store projects', function () {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    $payload = [
        'name' => fake()->name(),
        'titles' => [fake()->jobTitle(), fake()->jobTitle(), fake()->jobTitle()],
        'experiences' => [fake()->unique()->sentence(3), fake()->unique()->sentence(3)],
        'socials' => [
            ['name' => 'Facebook', 'url' => 'https://facebook.com'],
            ['name' => 'LinkedIn', 'url' => 'https://linkedin.com'],
        ],
        'theme_id' => ResumeTheme::first()->id,
        'contact' => ['show' => true, 'availability_status' => fake()->sentence()],
        'projects' => [
            [
                'title' => fake()->word(),
                'description' => fake()->sentence(5),
                'links' => [['name' => 'View More', 'url' => 'https://google.com']],
                'cover' => UploadedFile::fake()->image('fake_image.jpg', 500, 500)
            ]
        ],
    ];

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeContent'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    /** @var Resume $resume */
    $resume = $user->resume;

    $project = $resume->projects[0];
    expect($project['title'])->toEqual($payload['projects'][0]['title'])
        ->and($project['description'])->toEqual($payload['projects'][0]['description'])
        ->and($project['links'])->toEqual($payload['projects'][0]['links'])
        ->and($project['cover'])->toBeString();
});

it('can store work timelines', function (array $period) {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    $payload = [
        'name' => fake()->name(),
        'titles' => [fake()->jobTitle(), fake()->jobTitle(), fake()->jobTitle()],
        'experiences' => [fake()->unique()->sentence(3), fake()->unique()->sentence(3)],
        'socials' => [
            ['name' => 'Facebook', 'url' => 'https://facebook.com'],
            ['name' => 'LinkedIn', 'url' => 'https://linkedin.com'],
        ],
        'theme_id' => ResumeTheme::first()->id,
        'contact' => ['show' => true, 'availability_status' => fake()->sentence()],
        'work_timeline' => [
            'downloadable' => UploadedFile::fake()->create('fake_file.pdf', 500, 'application/pdf'),
            'history' => [
                [
                    'title' => fake()->word(),
                    'description' => fake()->sentence(2),
                    'period' => $period,
                    'company' => fake()->company(),
                    'logo' => 'https://google.com',
                    'tags' => [fake()->unique()->word(), fake()->unique()->word()],
                ]
            ]
        ],
    ];

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeContent'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    /** @var Resume $resume */
    $resume = $user->resume;

    expect($resume->work_timeline['history'])
        ->toEqual($payload['work_timeline']['history'])
        ->and($resume->work_timeline['downloadable'])
        ->toBeUrl();
})->with([
    'with start and end date' => ['period' => [fake()->date(), fake()->date()]],
    'without end date' => ['period' => [fake()->date()]],
]);

it('can store services', function () {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    $payload = [
        'name' => fake()->name(),
        'titles' => [fake()->jobTitle(), fake()->jobTitle(), fake()->jobTitle()],
        'experiences' => [fake()->unique()->sentence(3), fake()->unique()->sentence(3)],
        'socials' => [
            ['name' => 'Facebook', 'url' => 'https://facebook.com'],
            ['name' => 'LinkedIn', 'url' => 'https://linkedin.com'],
        ],
        'theme_id' => ResumeTheme::first()->id,
        'contact' => ['show' => true, 'availability_status' => fake()->sentence()],
        'services' => [
            [
                'title' => fake()->word(),
                'description' => fake()->sentence(3),
                'logo' => 'https://google.com',
                'tags' => [fake()->word(), fake()->word()],
            ]
        ]
    ];

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeContent'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    /** @var Resume $resume */
    $resume = $user->resume;

    expect($resume->services)->toEqual($payload['services']);
});
