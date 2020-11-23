<?php

/**
 * @file
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
 * Class GetNewMaterialsCommand.
 */
class GetNewMaterialsCommand extends Command
{
    protected static $defaultName = 'app:materials:get-new';

    private $newMaterialService;
    private $searchRepository;
    private $parameterBag;

    /**
     * OpenPlatformQueryCommand constructor.
     *
     * @param NewMaterialService $newMaterialService
     *   Service to query for new materials
     * @param SearchRepository $searchRepository
     *   Search entity repository
     * @param ParameterBagInterface $parameterBag
     *   Application configuration
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
    protected function configure(): void
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

        $count = 1;
        $total = \count($searches);
        foreach ($searches as $search) {
            if ($search) {
                $results = $this->newMaterialService->updateNewMaterialsSinceDate($search, $date);

                $output->writeln($count.'/'.$total.' - ['.$search->getId().'] '.$search->getName().': '.\count($results).' materiels found.');
            } else {
                $output->writeln('No CQL Search found with id = '.$id);
            }
            ++$count;
        }
    }
}
