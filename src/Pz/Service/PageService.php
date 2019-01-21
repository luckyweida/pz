<?php

namespace Pz\Service;

use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Router\Tree;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PageService
{
    /**
     * PageService constructor.
     * @param Container $container
     * @param $pageClass
     */
    public function __construct(Container $container, $pageClass)
    {
        $this->container = $container;
        $this->pageClass = $pageClass;
    }

    /**
     * @return mixed
     */
    public function getPageClass() {
        return $this->pageClass;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPages()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');

        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        return $this->pageClass::data($pdo, array(
            'whereSql' => 'm.status != 0',
        ));
    }

    /**
     * @param $categoryCode
     * @return null
     */
    public function nav($categoryCode)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');

        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $result = null;
        /** @var PageCategory $category */
        $category = PageCategory::getByField($pdo, 'code', $categoryCode);
        if ($category) {
            /** @var \Pz\Orm\Page[] $pages */
            $pages = $this->pageClass::data($pdo, array(
                'whereSql' => 'm.category LIKE ? ',
                'params' => array('%"' . $category->getId() . '"%'),
            ));

            foreach ($pages as $itm) {
                $categoryParent = !$itm->getCategoryParent() ? array() : (array)json_decode($itm->getCategoryParent());
                $categoryRank = !$itm->getCategoryRank() ? array() : (array)json_decode($itm->getCategoryRank());

                $parent = isset($categoryParent['cat' . $category->getId()]) ? $categoryParent['cat' . $category->getId()] : 0;
                $rank = isset($categoryRank['cat' . $category->getId()]) ? $categoryRank['cat' . $category->getId()] : 0;

                $itm->setParentId($parent);
                $itm->setRank($rank);
            }

            $tree = new Tree($pages);
            $result = $tree->getRoot();
        }

//        ini_set('xdebug.var_display_max_depth', '10');
//        ini_set('xdebug.var_display_max_children', '256');
//        ini_set('xdebug.var_display_max_data', '1024');
//        while (@ob_end_clean());
//        var_dump($result);exit;

        return $result;
    }
}