<?php

namespace Pz\Service;

use Pz\Form\Type\ContentBlock;
use Pz\Orm\FragmentBlock;
use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Orm\Product;
use Pz\Orm\ProductCategory;
use Pz\Router\Node;
use Pz\Router\Tree;
use Symfony\Component\DependencyInjection\Container;

class Shop
{
    /**
     * Shop constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getOrderContainer() {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $orderContainer = $this->container->get('session')->get('orderContainer');
        if (!$orderContainer) {
            $orderContainer = new Order($pdo);
            $this->container->get('session')->set('orderContainer', $orderContainer);
        }
        return $orderContainer;
    }

    public function total($selectedCategories, $options = array())
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $params = $this->getBasicParams($selectedCategories, $options);
        $result = Product::active($pdo, array_merge($params, array(
            'count' => 1,
        )));
        return $result['count'];
    }

    public function products($selectedCategories, $options = array())
    {
        $pagination = isset($options['pagination']) ? $options['pagination'] : 1;
        $limit = isset($options['limit']) ? $options['limit'] : 12;

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $params = $this->getBasicParams($selectedCategories, $options);
        return Product::active($pdo, array_merge($params, array(
            'sort' => 'm.myRank',
            'order' => 'DESC, m.title ASC',
            'page' => $pagination,
            'limit' => $limit,
        )));
    }

    public function getBasicParams($selectedCategories, $options = array())
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $special = isset($options['special']) && $options['special'] == 1 ? 1 : 0;

        $sql = 'm.variantProduct != 1 OR variantProduct IS NULL';
        $params = array();
        if (count($selectedCategories)) {
            $catSql = '';
            $catParams = array();

            $categoryIds = array();
            foreach ($selectedCategories as $selectedCategory) {
                $orm = ProductCategory::getByField($pdo, 'slug', $selectedCategory);
                if ($orm) {
                    $categoryIds = array_merge($categoryIds, $orm->getIdAndDescendantIds());
                }
            }
            $categoryIds = array_unique($categoryIds);
            foreach ($categoryIds as $categoryId) {
                $catSql .= ($catSql ? ' OR ' : '') . 'm.category LIKE ?';
                $catParams[] = '%"' . $categoryId . '"%';
            }

            if ($catSql) {
                $sql .= ($sql ? ' AND ' : '') . "($catSql)";
                $params = array_merge($params, $catParams);
            }
        }

        if ($special) {
            $sql .= ($sql ? ' AND ' : '') . 'm.onSpecial = 1';
        }

        return array(
            'whereSql' => $sql,
            'params' => $params,
        );
    }
}