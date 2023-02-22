<?php

namespace App\Controller\Admin;

use App\Admin\Field\CqlResultField;
use App\Admin\Field\SuccesField;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\OpenPlatform\NewMaterialService;
use App\Service\OpenPlatform\SearchService;
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
    private SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

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
}
