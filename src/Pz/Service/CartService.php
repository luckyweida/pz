<?php

namespace Pz\Service;

use Pz\Orm\Customer;
use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\Product;
use Pz\Orm\ProductCategory;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CartService
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
        if (!$orderContainer || $orderContainer->getPayStatus() != CartService::STATUS_UNPAID()) {
            $orderContainer = new Order($pdo);
            $this->container->get('session')->set('orderContainer', $orderContainer);
        }

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        /** @var Customer $customer */
        $customer = $tokenStorage->getToken()->getUser();
        if (!$orderContainer->getEmail() && gettype($customer) == 'object') {
            $orderContainer->setEmail($customer->getTitle());
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
     * @return int
     */
    static public function STATUS_UNPAID() {
        return 0;
    }

    /**
     * @return int
     */
    static public function STATUS_SUBMITTED() {
        return 1;
    }

    /**
     * @return int
     */
    static public function STATUS_SUCCESS() {
        return 2;
    }

    /**
     * @return int
     */
    static public function DELIVERY_HIDDEN() {
        return 0;
    }

    /**
     * @return int
     */
    static public function DELIVERY_VISIBLE() {
        return 1;
    }
}