<?php

/**
 * @file
 */

namespace App\Command;

use App\Service\Heyloyalty\HeyloyaltyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class HeyloyaltyCommand.
 */
class HeyloyaltyTestCommand extends Command
{
    protected static $defaultName = 'app:heyloyalty:test';

    /**
     * HeyloyaltyCommand constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param HeyloyaltyService $hlService
     */
    public function __construct(private readonly ParameterBagInterface $parameterBag, private readonly HeyloyaltyService $hlService)
    {
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
