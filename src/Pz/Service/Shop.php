<?php

namespace Pz\Service;

use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\Product;
use Pz\Orm\ProductCategory;

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

    /**
     * @return Order
     * @throws \Exception
     */
    public function getOrderContainer() {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        /** @var Order $orderContainer */
        $orderContainer = $this->container->get('session')->get('orderContainer');
        if (!$orderContainer || $orderContainer->getPayStatus() != Order::STATUS_UNPAID) {
            $orderContainer = new Order($pdo);
            $this->container->get('session')->set('orderContainer', $orderContainer);
        }


        //ORDER: Load order items
        foreach ($orderContainer->getOrderItems() as $orderItem) {
            $exist = false;
            foreach ($orderContainer->getPendingItems() as $pendingItem) {
                if ($pendingItem->getUniqid() == $orderItem->getUniqid()) {
                    $exist = true;
                }
            }
            if (!$exist) {
                $orderContainer->addPendingItem($orderItem);
            }
        }

//        var_dump($orderContainer);exit;
        return $orderContainer;
    }

    /**
     * @param $selectedCategories
     * @param array $options
     * @return array|null
     * @throws \Exception
     */
    public function getProducts($selectedCategories, $options = array())
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

    /**
     * @param $selectedCategories
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function getProductsTotal($selectedCategories, $options = array())
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

    /**
     * @param $selectedCategories
     * @param array $options
     * @return array
     * @throws \Exception
     */
    private function getBasicParams($selectedCategories, $options = array())
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