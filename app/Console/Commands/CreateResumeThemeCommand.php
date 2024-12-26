<?php

namespace App\Console\Commands;

use App\Models\ResumeTheme;
use App\Rules\DbVarcharMaxLengthRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\error;

class CreateResumeThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resume:create-theme
                           {--N|name= : The name of the theme. E.g.: --name="Gradient"}
                           {--P|page= : The Vue file page name in Pages/Portfolio/ directory. E.g.: --page="GradientThemePage"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resume theme.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $data = [
            'name' => $this->option('name'),
            'page' => $this->option('page'),
        ];

        try {
            Validator::validate(
                $data,
                [
                    'name' => ['required', 'string', new DbVarcharMaxLengthRule(), 'unique:resume_themes,name'],
                    'page' => ['required', 'string', new DbVarcharMaxLengthRule(), 'unique:resume_themes,page', 'regex:/^[a-zA-Z0-9_.-]*$/' // Letters and Numbers only (this will be used as the file name)
                ],
            ],
                messages: ['page' => 'The page name must be a valid file name (Letters and numbers only)']
            );
        } catch (ValidationException $e) {
            error($e->getMessage());

            return Command::FAILURE;
        }

        $theme = ResumeTheme::query()->create($data);
        $this->info("Resume theme created successfully: $theme->name");
        return Command::SUCCESS;
    }
}
