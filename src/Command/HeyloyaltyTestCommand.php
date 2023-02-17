<?php

/**
 * @file
 */

namespace App\Command;

use App\Service\Heyloyalty\HeyloyaltyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class HeyloyaltyCommand.
 */
#[AsCommand('app:heyloyalty:test')]
class HeyloyaltyTestCommand extends Command
{
    /**
     * HeyloyaltyCommand constructor.
     */
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly HeyloyaltyService $hlService
    ) {
        parent::__construct();
    }

    /**
     * Define the command.
     */
    protected function configure(): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * Execute an data well search and output the result.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->hlService->addOption('TEST 42');
    }
}
