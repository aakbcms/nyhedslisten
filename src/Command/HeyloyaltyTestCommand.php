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

    private ParameterBagInterface $parameterBag;
    private HeyloyaltyService $hlService;

    /**
     * HeyloyaltyCommand constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param HeyloyaltyService $hlService
     */
    public function __construct(ParameterBagInterface $parameterBag, HeyloyaltyService $hlService)
    {
        $this->parameterBag = $parameterBag;
        $this->hlService = $hlService;

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
     * Execute a data well search and output the result.
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->hlService->addOption('TEST 42');
    }
}
