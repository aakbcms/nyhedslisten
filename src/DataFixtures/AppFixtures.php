<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Material;
use App\Entity\SearchRun;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Categories
        $json = \file_get_contents(__DIR__.'/Data/categories.json');
        $data = \json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        $categories = [];

        foreach ($data as $datum) {
            $category = new Category();
            $category->setName($datum->name)
                ->setCqlSearch($datum->cql_search);

            $manager->persist($category);

            $categories[$datum->id] = $category;
        }

        // Search Runs
        $json = \file_get_contents(__DIR__.'/Data/search_runs.json');
        $data = \json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        foreach ($data as $datum) {
            $category = $categories[$datum->category_id];
            $runAt = new \DateTimeImmutable($datum->run_at);
            $searchRun = new SearchRun($category, $runAt);
            $searchRun->setIsSuccess((bool) $datum->is_success)
                ->setErrorMessage($datum->error_message);

            $manager->persist($searchRun);
        }

        // Materials
        $json = \file_get_contents(__DIR__.'/Data/materials.json');
        $data = \json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        foreach ($data as $datum) {
            $material = new Material();

            $material->setTitleFull($datum->title_full)
                ->setCreator($datum->creator)
                ->setAbstract($datum->abstract)
                ->setPid($datum->pid)
                ->setPublisher($datum->publisher)
                ->setDate(new \DateTimeImmutable($datum->date))
                ->setUri($datum->uri)
                ->setCoverUrl($datum->cover_url)
                ->setCreatorAut($datum->creator_aut)
                ->setCreatorCre($datum->creator_cre)
                ->setContributor($datum->contributor)
                ->setContributorAct($datum->contributor_act)
                ->setContributorAut($datum->contributor_aut)
                ->setContributorCtb($datum->contributor_ctb)
                ->setContributorDkfig($datum->contributor_dkfig)
                ->setType($datum->type);

            $categoryIds = array_map('intval', explode(',', $datum->categories));

            foreach ($categoryIds as $categoryId) {
                $material->addCategory($categories[$categoryId]);
            }

            $manager->persist($material);
        }

        $manager->flush();
    }
}
