<?php

namespace App\Command;

use App\Repository\SearchRunRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:search-runs:purge',
    description: 'Purge old search runs before given date. Default older than 30 days.',
)]
class SearchRunsPurgeCommand extends Command
{
    private const DEFAULT_BEFORE = '-30 days';

    public function __construct(
        private readonly SearchRunRepository $searchRunRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('before', null, InputOption::VALUE_REQUIRED, 'Purge all search runs older than date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('before')) {
            $before = \DateTime::createFromFormat('Y-m-d', $input->getOption('before'));

            if (false === $before) {
                $io->error(sprintf('Unknown date format "%s". Please use "yyyy-mm-dd"', $input->getOption('before')));

                return Command::FAILURE;
            }
        } else {
            $before = new \DateTime(self::DEFAULT_BEFORE);
        }

        $rowCount = $this->searchRunRepository->deleteBefore($before);

        $io->success(sprintf('Purged %d search runs from db', $rowCount));

        return Command::SUCCESS;
    }
}
