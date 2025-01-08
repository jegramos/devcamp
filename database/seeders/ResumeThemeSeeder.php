<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResumeThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Gradient',
                'page' => 'GradientThemePage',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('resume_themes')->insert($themes);
    }
}
