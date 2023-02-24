<?php

namespace App\Command;

use App\Entity\Material;
use App\Repository\MaterialRepository;
use App\Service\DdbUriService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:ddbcms-url:update',
    description: 'Update DDB CMS Url for al materials',
)]
class DdbcmsUrlUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MaterialRepository $materialRepository,
        private readonly DdbUriService $ddbUriService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->materialRepository->count([]);
        $progressBar = new ProgressBar($output, $count);
        $progressBar->start();

        $qb = $this->materialRepository->createQueryBuilder('m', 'm.pid');

        $offset = 0;
        do {
            $qb->setFirstResult($offset);

            /** @var Material[] $materials */
            $materials = $qb->getQuery()->getResult();

            foreach ($materials as $pid => $material) {
                $material->setUri($this->ddbUriService->getUri($pid));
                $progressBar->advance();
            }

            $offset += \count($materials);

            $this->entityManager->flush();
        } while (!empty($materials));

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
