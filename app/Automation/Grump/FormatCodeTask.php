<?php

namespace App\Automation\Grump;

use App\Console\Commands\FormatCodeCommand;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom GrumPHP task for code formatting using the \App\Console\Commands\FormatCode command class.
 * This task integrates with GrumPHP to format code before commits and during run contexts.
 *
 * Example GrumPHP Configuration (grumphp.yml):
 * <code>
 * grumphp:
 *   tasks:
 *     format_code:
 *       with_ide_helper: true
 * services:
 *   App\Automation\Grump\FormatCodeTask:
 *     arguments:
 *       - '{@}process_builder'
 *       - '{@}formatter.raw_process'
 *     tags:
 *       - { name: grumphp.task, task: format_code, priority: 1 }
 * </code>
 *
 * @see https://github.com/phpro/grumphp/blob/v2.x/doc/tasks.md#run-the-same-task-twice-with-different-configuration
 */
class FormatCodeTask extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'with_ide_helper' => true,
        ]);

        $resolver->addAllowedTypes('with_ide_helper', ['null', 'boolean']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        /** @see \App\Console\Commands\CodeFormatter */
        $config = $this->getConfig()->getOptions();
        $command = 'php artisan app:format-code';

        if (! $config['with_ide_helper']) {
            $command .= ' --no-ide-helper';
        }

        exec($command, $output, $exitCode);

        foreach ($output as $message) {
            echo $message . PHP_EOL;
        }

        if ($exitCode !== Command::SUCCESS) {
            $formatCodeClass = FormatCodeCommand::class;
            $errorMessage =
                "A command threw an exception (code: $exitCode)  in $formatCodeClass. Please see the logs above";

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}
