<?php

namespace App\Controller\Admin;

use App\Admin\Field\CqlResultField;
use App\Admin\Field\SuccesField;
use App\Entity\Category;
use App\Exception\HeyloyaltyException;
use App\Exception\HeyloyaltyOptionNotFoundException;
use App\Repository\CategoryRepository;
use App\Service\Heyloyalty\HeyloyaltyService;
use App\Service\OpenPlatform\NewMaterialService;
use App\Service\OpenPlatform\SearchService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class CategoryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SearchService $searchService,
        private readonly HeyloyaltyService $heyloyaltyService,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->addBatchAction(Action::new('query', 'Query Open Search')
                ->linkToCrudAction('queryBatchAction')
                ->addCssClass('btn btn-primary')
                ->setIcon('fa fa-search')
            );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Category')
            ->setEntityLabelInPlural('Category')
            ->setSearchFields(['name', 'cqlSearch', 'createdBy', 'updatedBy']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Category');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield DateTimeField::new('lastSearchRunAt', 'Last run')->hideOnForm();
        yield CodeEditorField::new('cqlSearch', 'CQL search')
            ->hideOnIndex()
            ->hideLineNumbers()
            ->setHelp('Do not include "holdingsitem.accessionDate" ("bad") or "facet.acSource" parameters in the CQL statement. These will be added automatically.');
        yield CqlResultField::new('cqlSearch', 'CQL result')
            ->setHelp('Result from running the search (Limit 30)')
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                $value ?? '';
                try {
                    $v = $this->searchService->query($value, 30);
                } catch (\Exception $exception) {
                    $v = $exception;
                }

                return $v;
            });

        yield FormField::addTab('Search runs')->hideOnForm();
        yield SuccesField::new('lastSearchRunSuccess', 'Succes')->hideOnForm();
        yield AssociationField::new('searchRuns')->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name');
    }

    public function queryBatchAction(BatchActionDto $batchActionDto, NewMaterialService $materialService, CategoryRepository $categoryRepository)
    {
        $ids = array_map(static function ($id) {
            return (int) $id;
        }, $batchActionDto->getEntityIds());
        $searches = $categoryRepository->findBy(['id' => $ids]);

        // @TODO Move time interval to config
        $date = new \DateTimeImmutable('7 days ago');

        $materialCount = 0;
        foreach ($searches as $search) {
            $result = $materialService->updateNewMaterialsSinceDate($search, $date);

            $materialCount += \count($result);
        }

        if (!empty($searches)) {
            $this->addFlash(
                'info',
                \count($searches).' CQL queries performed, '.$materialCount.' materials fetched'
            );
        }

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $d = $adminUrlGenerator
            ->setController(CategoryCrudController::class)->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($d);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $uof = $entityManager->getUnitOfWork();
        $originalEntity = $uof->getOriginalEntityData($entityInstance);

        // We only need to update if the name has changed. The CQL search is not used
        // by Heyloyalty
        if ($originalEntity['name'] !== $entityInstance->getName()) {
            try {
                $this->heyloyaltyService->updateOption($originalEntity['name'], $entityInstance->getName());

                $this->flashSuccess('Update', $entityInstance->getName());

                parent::updateEntity($entityManager, $entityInstance);
            } catch (HeyloyaltyOptionNotFoundException $e) {
                try {
                    $this->heyloyaltyService->addOption($entityInstance->getName());

                    $this->flashSuccess('Update', $entityInstance->getName());

                    parent::updateEntity($entityManager, $entityInstance);
                } catch (HeyloyaltyException $e) {
                    $this->flashError('Update', $entityInstance->getName(), $e);
                }
            } catch (HeyloyaltyException $e) {
                $this->flashError('Update', $entityInstance->getName(), $e);
            }
        } else {
            parent::updateEntity($entityManager, $entityInstance);
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            $this->heyloyaltyService->addOption($entityInstance->getName());

            $this->flashSuccess('Persist', $entityInstance->getName());

            parent::persistEntity($entityManager, $entityInstance);
        } catch (\Exception $e) {
            $this->flashError('Persist', $entityInstance->getName(), $e);
        }
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            $this->heyloyaltyService->removeOption();

            $this->flashSuccess('Delete', $entityInstance->getName());

            parent::deleteEntity($entityManager, $entityInstance);
        } catch (\Exception $e) {
            $this->flashError('Delete', $entityInstance->getName(), $e);
        }
    }

    private function flashSuccess(string $action, string $name): void
    {
        $this->addFlash(
            'success',
            sprintf('%s "%s" successfull in Heyloyalty.', $action, $name)
        );
    }

    private function flashError(string $action, string $name, \Exception $e): void
    {
        $this->addFlash(
            'danger',
            sprintf('%s "%s" failed in Heyloyalty. [%d: %s]', $action, $name, $e->getCode(), $e->getMessage())
        );
    }
}
