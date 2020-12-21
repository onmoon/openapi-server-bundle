<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

use function Safe\sprintf;

final class RefreshApiCodeCommand extends Command
{
    private string $rootPath;
    private ProcessFactory $processFactory;

    public function __construct(string $rootPath, ProcessFactory $processFactory, ?string $name = null)
    {
        $this->rootPath       = $rootPath;
        $this->processFactory = $processFactory;

        parent::__construct($name);
    }

    /**
     * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'open-api:refresh';

    protected function configure(): void
    {
        $this->setDescription('Refreshes API server code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->isConfirmed($input, $output)) {
            return 0;
        }

        $deleteCommandProcess   = $this->processFactory->getProcess(['php', 'bin/console', DeleteGeneratedCodeCommand::COMMAND, '-y']);
        $generateCommandProcess = $this->processFactory->getProcess(['php', 'bin/console', GenerateApiCodeCommand::COMMAND]);

        return $deleteCommandProcess->run($this->processOutputHandler($output)) === 0 &&
        $generateCommandProcess->run($this->processOutputHandler($output)) === 0 ?
            0 :
            1;
    }

    private function isConfirmed(InputInterface $input, OutputInterface $output): bool
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new ConfirmationQuestion(
            sprintf(
                'Delete all contents of the directory %s? (y/n): ',
                $this->rootPath
            ),
            false
        );

        return (bool) $questionHelper->ask($input, $output, $question);
    }

    private function processOutputHandler(OutputInterface $output): callable
    {
        return static function (string $type, string $data) use ($output): void {
            if ($type === Process::ERR && $output instanceof ConsoleOutputInterface) {
                $output->getErrorOutput()->writeln($data);

                return;
            }

            $output->writeln($data);
        };
    }
}
