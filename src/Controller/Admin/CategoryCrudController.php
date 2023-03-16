<?php

namespace App\Controller\Admin;

use App\Admin\Field\CqlResultField;
use App\Admin\Field\ListHasField;
use App\Admin\Field\SuccessField;
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
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CategoryCrudController extends AbstractCrudController
{
    /**
     * CategoryCrudController constructor.
     *
     * @param SearchService $searchService
     * @param HeyloyaltyService $heyloyaltyService
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly SearchService $searchService,
        private readonly HeyloyaltyService $heyloyaltyService,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncToHeyloyalty = Action::new('addToHeyloyalty')
            ->linkToCrudAction('addToHeyloyalty');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $syncToHeyloyalty)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            // @TODO Fix Heyloyalty delete
            ->disable(Action::DELETE)
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
        yield IdField::new('id')->hideOnIndex()->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        yield ListHasField::new('hlName', 'HL')
            ->setSortable(false)
            ->setVirtual(true)
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                try {
                    $d = $this->heyloyaltyService->hasOption($value);

                    return $d;
                } catch (HeyloyaltyException $exception) {
                    return null;
                }
            });
        yield CodeEditorField::new('cqlSearch', 'CQL search')
            ->hideOnIndex()
            ->hideLineNumbers()
            ->setHelp('Do not include "holdingsitem.accessionDate" ("bad") or "facet.acSource" parameters in the CQL statement. These will be added automatically.');
        yield TextField::new('createdBy')->hideOnIndex()->setDisabled()->setColumns(3);
        yield DateTimeField::new('createdAt')->hideOnIndex()->setDisabled()->setColumns(3);
        yield TextField::new('updatedBy')->hideOnIndex()->setDisabled()->setColumns(3);
        yield DateTimeField::new('updatedAt')->hideOnIndex()->setDisabled()->setColumns(3);

        yield FormField::addTab('CQL result')->hideOnForm();
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
        yield SuccessField::new('lastSearchRunSuccess', 'Success')->hideOnForm();
        yield DateTimeField::new('lastSearchRunAt', 'Last run')->hideOnForm();
        yield AssociationField::new('searchRuns')->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name');
    }

    /**
     * Update entity hook.
     *
     * @param EntityManagerInterface $entityManager
     * @param $entityInstance
     *
     * @return void
     */
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
            } catch (HeyloyaltyException $e) {
                $this->flashError('Update', $entityInstance->getName(), $e);
            }
        } else {
            parent::updateEntity($entityManager, $entityInstance);
        }
    }

    /**
     * Persist entity hook.
     *
     * @param EntityManagerInterface $entityManager
     * @param $entityInstance
     *
     * @return void
     */
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

    /**
     * Batch query the datawell for selected categories.
     *
     * @param BatchActionDto $batchActionDto
     * @param NewMaterialService $materialService
     * @param CategoryRepository $categoryRepository
     *
     * @return RedirectResponse
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function queryBatchAction(BatchActionDto $batchActionDto, NewMaterialService $materialService, CategoryRepository $categoryRepository): RedirectResponse
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

    /**
     * Add category to Heyloyalty.
     *
     * @param AdminContext $adminContext
     *
     * @return RedirectResponse
     */
    public function addToHeyloyalty(AdminContext $adminContext): RedirectResponse
    {
        /** @var Category $category */
        $category = $adminContext->getEntity()->getInstance();

        try {
            // Ensure option doesn't exist already
            $this->heyloyaltyService->updateOption($category->getName(), $category->getName());

            $this->addFlash('warning', sprintf('Category %s already exists in Heyloyalty', $category->getName()));
        } catch (HeyloyaltyOptionNotFoundException $e) {
            // If option doesn't exist, then add it
            try {
                $this->heyloyaltyService->addOption($category->getName());

                $this->flashSuccess('Persist', $category);
            } catch (HeyloyaltyException $e) {
                $this->flashError('Persist', $category->getName(), $e);
            }
        } catch (HeyloyaltyException $e) {
            $this->flashError('Persist', $category->getName(), $e);
        }

        return $this->redirect($adminContext->getReferrer());
    }

    /**
     * Add flash success message.
     *
     * @param string $action
     * @param string $name
     *
     * @return void
     */
    private function flashSuccess(string $action, string $name): void
    {
        $this->addFlash(
            'success',
            sprintf('%s "%s" successfull in Heyloyalty.', $action, $name)
        );
    }

    /**
     * Add flash error message.
     *
     * @param string $action
     * @param string $name
     * @param \Exception $e
     *
     * @return void
     */
    private function flashError(string $action, string $name, \Exception $e): void
    {
        $this->addFlash(
            'danger',
            sprintf('%s "%s" failed in Heyloyalty. [%d: %s]', $action, $name, $e->getCode(), $e->getMessage())
        );
    }
}
