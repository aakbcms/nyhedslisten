<?php

namespace App\Command;

use App\Entity\Material;
use App\Repository\MaterialRepository;
use App\Service\CoverServiceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:covers:update',
    description: 'Update covers for all materials',
)]
class CoversUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MaterialRepository $materialRepository,
        private readonly CoverServiceService $coverService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'ID for the material to update')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Offset for db search', 0)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit for db serach (batch size)', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getOption('id');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');

        $count = $this->materialRepository->count([]);
        $progressBar = new ProgressBar($output, $count);
        $progressBar->start();

        $qb = $this->materialRepository->createQueryBuilder('m', 'm.pid');
        if ($id) {
            $qb->where('m.id = :id')->setParameter('id', $id);
        }

        $qb->setMaxResults($limit);

        do {
            $qb->setFirstResult($offset);

            /** @var Material[] $materials */
            $materials = $qb->getQuery()->getResult();

            $covers = $this->coverService->getCovers(array_keys($materials));

            foreach ($materials as $pid => $material) {
                $material->setCoverUrl($covers[$pid] ?? $this->coverService->getGenericCoverUrl($material));
                $progressBar->advance();
            }

            $offset += \count($materials);

            $this->entityManager->flush();
        } while (!empty($materials));

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
