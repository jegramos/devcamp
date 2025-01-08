<?php

use App\Models\ResumeTheme;

use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertEquals;

it('can create a new theme', function () {
    $name = 'TestTheme';
    $page = 'TestPage';
    artisan("resume:create-theme --name='$name' --page='$page'")
        ->assertExitCode(0);

    $theme = ResumeTheme::query()->where('name', 'TestTheme')->latest()->first();
    assertEquals($theme->name, $name);
});

it('can validate name and page', function (string $name, string $page) {
    ResumeTheme::query()->create(['name' => 'TestTheme', 'page' => 'TestPage']);
    artisan("resume:create-theme --name='$name' --page='$page'")
        ->assertExitCode(1);
})->with([
    'not unique name' => ['name' => 'TestTheme', 'page' => 'TestTwoPage'],
    'not unique page' => ['name' => 'TestTwoTheme', 'page' => 'TestPage'],
    'page with space' => ['name' => 'TestTheme', 'page' => 'Test Page'],
    'page with special characters' => ['name' => 'TestTheme', 'page' => '!Test@Page'],
    'page with extra dots' => ['name' => 'TestTheme', 'page' => 'Test.Page'],
]);
