<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class FormatCodeCommand extends Command
{
    protected $signature = 'app:format-code
                           {--no-ide-helper : Run the code formatter without barryvdh/laravel-ide-helper}
                           {--no-git-add    : Do not automatically run the command `git add .` after a successful format}
                           {--dry-run       : Inspect code style errors without changing the files}';

    protected $description = 'Enforces Laravel coding standards and enhances IDE integration';

    public function handle(): int
    {
        /**
         * Laravel Pint is used as the code styler
         *
         * @see https://laravel.com/docs/11.x/pint#main-content
         */
        $pintTestArg = $this->option('dry-run') ? '--test' : '';
        $dirSep = DIRECTORY_SEPARATOR;
        $pintCommand = "vendor{$dirSep}bin{$dirSep}pint $pintTestArg";

        info("\u{1F9F9} Running Pint...");
        $exitCode = Command::SUCCESS;

        exec($pintCommand, $output, $exitCode);

        foreach ($output as $message) {
            $this->info($message);
        }

        /**
         * An IDE helper generator package is used for improved intellisense is used
         *
         * @see https://github.com/barryvdh/laravel-ide-helper
         */
        $ideHelperCommands = [];
        if (!$this->option('no-ide-helper')) {
            $ideHelperCommands[] = ['cmd' => 'clear-compiled', 'args' => []];
            $ideHelperCommands[] = ['cmd' => 'ide-helper:generate', 'args' => []];
            $ideHelperCommands[] = ['cmd' => 'ide-helper:meta', 'args' => []];
            $ideHelperCommands[] = ['cmd' => 'ide-helper:models', 'args' => ['--nowrite' => true]];

            info("\u{1F4BB} Running IDE helper generator...");
        }

        foreach ($ideHelperCommands as $ideHelperCommand) {
            try {
                $this->call($ideHelperCommand['cmd'], $ideHelperCommand['args']);
            } catch (Throwable $th) {
                error("\u{1F645}  Error: " . $th->getMessage());
                $exitCode = Command::FAILURE;
            }
        }

        // Stage the code changes in Git if successful
        $dryRun = (bool) $this->option('dry-run');
        $noGitAdd = (bool) $this->option('no-git-add');
        if ($exitCode === Command::SUCCESS && !$dryRun && !$noGitAdd) {
            exec('git add .');
            info("\u{2795}  Any code changes were staged in Git");
        }

        if ($exitCode === Command::SUCCESS) {
            info("\u{1F44D} Done!");
        }

        return $exitCode;
    }
}
