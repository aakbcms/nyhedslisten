<?php

/**
 * @file
 * Console commands to execute and test Open Platform search.
 */

namespace App\Command;

use App\Repository\SearchRepository;
use App\Service\OpenPlatform\NewMaterialService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class OpenPlatformSearchCommand.
 */
class GetNewMaterialsCommand extends Command
{
    protected static $defaultName = 'app:openplatform:query';

    private $newMaterialService;
    private $searchRepository;
    private $parameterBag;

    /**
     * OpenPlatformQueryCommand constructor.
     *
     * @param NewMaterialService $newMaterialService
     * @param SearchRepository   $searchRepository
     */
    public function __construct(NewMaterialService $newMaterialService, SearchRepository $searchRepository, ParameterBagInterface $parameterBag)
    {
        $this->newMaterialService = $newMaterialService;
        $this->searchRepository = $searchRepository;
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    /**
     * Define the command.
     */
    protected function configure()
    {
        $this->setDescription('Get all new materials received in configured timespan')
            ->setHelp('Searches through OpenSearch to get all materials received within the time interval configured for the application')
            ->addArgument('id', InputArgument::OPTIONAL, 'The ID of the CQL search to run');
    }

    /**
     * {@inheritdoc}
     *
     * Execute an data well search and output the result.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        if ($id) {
            $searches = $this->searchRepository->findById($id);
        } else {
            $searches = $this->searchRepository->findAll();
        }

        $dateConfig = $this->parameterBag->get('datawell.default.accessiondate.criteria');
        $date = new \DateTimeImmutable($dateConfig);

        foreach ($searches as $search) {
            if ($search) {
                $results = $this->newMaterialService->persistNewMaterialsSinceDate($search, $date);

                $output->writeln(\count($results));
            } else {
                $output->writeln('No CQL Search found with id = '.$id);
            }
        }
    }
}
