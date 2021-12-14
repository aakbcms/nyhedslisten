<?php

/**
 * @file
 */

namespace App\Command;

use App\Repository\CategoryRepository;
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

    private NewMaterialService $newMaterialService;
    private CategoryRepository $categoryRepository;
    private ParameterBagInterface $parameterBag;

    /**
     * OpenPlatformQueryCommand constructor.
     *
     * @param NewMaterialService $newMaterialService
     *   Service to query for new materials
     * @param CategoryRepository $categoryRepository
     *   Search entity repository
     * @param ParameterBagInterface $parameterBag
     *   Application configuration
     */
    public function __construct(NewMaterialService $newMaterialService, CategoryRepository $categoryRepository, ParameterBagInterface $parameterBag)
    {
        $this->newMaterialService = $newMaterialService;
        $this->categoryRepository = $categoryRepository;
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
            $categories = $this->categoryRepository->findById($id);
        } else {
            $categories = $this->categoryRepository->findAll();
        }

        $dateConfig = $this->parameterBag->get('datawell.default.accessiondate.criteria');
        $date = new \DateTimeImmutable($dateConfig);

        $count = 1;
        $total = \count($categories);
        foreach ($categories as $category) {
            if ($category) {
                $results = $this->newMaterialService->updateNewMaterialsSinceDate($category, $date);

                $output->writeln($count.'/'.$total.' - ['.$category->getId().'] '.$category->getName().': '.\count($results).' materiels found.');
            } else {
                $output->writeln('No CQL Search found with id = '.$id);
            }
            ++$count;
        }
    }
}
