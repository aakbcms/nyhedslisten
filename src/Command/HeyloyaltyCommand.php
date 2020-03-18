<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Service\Heyloyalty\HeyloyaltyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class HeyloyaltyCommand.
 */
class HeyloyaltyCommand extends Command
{
    protected static $defaultName = 'app:heyloyalty';

    private $parameterBag;
    private $hlService;

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
     * Execute an data well search and output the result.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->hlService->addOption('TEST 42');
    }
}
