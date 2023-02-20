<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Material;
use App\Entity\SearchRun;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {}

    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        $d = $this->adminUrlGenerator
            ->setController(CategoryCrudController::class)->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($d);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('AAKB Nyhedslister')
            ->renderContentMaximized();
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm:ss')
            ->setTimeFormat('HH:mm');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Category', 'fa fa-search', Category::class);
        yield MenuItem::linkToCrud('Material', 'fa fa-book', Material::class);
        yield MenuItem::section('');
        yield MenuItem::linkToCrud('Search Runs', 'fa fa-gear', SearchRun::class);
    }
}
