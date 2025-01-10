<?php

use App\Actions\CreateOrUpdateResumeAction;
use App\Models\ResumeTheme;
use App\Services\CloudStorageManager;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    artisan('db:seed');
});

it('can create a resume record for the user', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    $data = [
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

    $cloudStorageManager = resolve(CloudStorageManager::class);
    $action = new CreateOrUpdateResumeAction($cloudStorageManager);
    $action->execute($user, $data);
    $user->refresh();

    assertDatabaseCount('resumes', 1);

    expect($user->resume->name)->toBe($data['name'])
        ->and($user->resume->titles)->toEqual($data['titles'])
        ->and($user->resume->experiences)->toEqual($data['experiences'])
        ->and($user->resume->socials)->toEqual($data['socials'])
        ->and($user->resume->theme_id)->toEqual($data['theme_id'])
        ->and($user->resume->contact)->toEqual($data['contact']);
});

it('can update a resume record for the user', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    $data = [
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
        ],
        'projects' => [
            [
                'title' => fake()->word(),
                'description' => fake()->sentence(5),
                'links' => [['name' => 'View More', 'url' => 'https://google.com']],
                'cover' => UploadedFile::fake()->image('fake_image.jpg', 500, 500)
            ]
        ],
        'work_timeline' => [
            'downloadable' => UploadedFile::fake()->create('fake_file.pdf', 500, 'application/pdf'),
            'history' => [
                [
                    'title' => fake()->word(),
                    'description' => fake()->sentence(2),
                    'period' => ['2024-01-01', '2024-02-01'],
                    'company' => fake()->company(),
                    'logo' => 'https://google.com',
                    'tags' => [fake()->unique()->word(), fake()->unique()->word()],
                ]
            ]
        ],
        'services' => [
            [
                'title' => fake()->word(),
                'description' => fake()->sentence(3),
                'logo' => 'https://google.com',
                'tags' => [fake()->word(), fake()->word()],
            ]
        ]
    ];

    $cloudStorageManager = resolve(CloudStorageManager::class);
    $action = new CreateOrUpdateResumeAction($cloudStorageManager);
    $action->execute($user, $data);
    $user->refresh();

    expect($user->resume->name)->toBe($data['name'])
        ->and($user->resume->titles)->toEqual($data['titles'])
        ->and($user->resume->experiences)->toEqual($data['experiences'])
        ->and($user->resume->socials)->toEqual($data['socials'])
        ->and($user->resume->theme_id)->toEqual($data['theme_id'])
        ->and($user->resume->contact)->toEqual($data['contact'])
        ->and($user->resume->tech_expertise)->toEqual($data['tech_expertise'])
        ->and($user->resume->projects[0]['title'])->toEqual($data['projects'][0]['title'])
        ->and($user->resume->projects[0]['description'])->toEqual($data['projects'][0]['description'])
        ->and($user->resume->projects[0]['links'])->toEqual($data['projects'][0]['links'])
        ->and($user->resume->projects[0]['cover'])->toBeString()
        ->and($user->resume->work_timeline['downloadable'])->toBeString()
        ->and($user->resume->work_timeline['downloadable_url'])->toBeUrl()
        ->and($user->resume->work_timeline['history'])->toEqual($data['work_timeline']['history'])
        ->and($user->resume->services)->toEqual($data['services']);
});
