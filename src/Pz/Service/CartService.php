<?php

namespace Pz\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CartService
{
    const STATUS_UNPAID = 0;
    const STATUS_SUBMITTED = 1;
    const STATUS_SUCCESS = 2;

    const DELIVERY_HIDDEN = 0;
    const DELIVERY_VISIBLE = 1;

    const CUSTOMER_WEBSITE = 1;
    const CUSTOMER_GOOGLE = 2;
    const CUSTOMER_FACEBOOK = 3;

    protected $orderContainer;

    /**
     * Shop constructor.
     * @param Container $container
     */
    public function __construct(Container $container, $productClass, $orderClass, $orderItemClass, $customerClass, $customerAddressClass)
    {
        $this->container = $container;
        $this->productClass = $productClass;
        $this->orderClass = $orderClass;
        $this->orderItemClass = $orderItemClass;
        $this->customerClass = $customerClass;
        $this->customerAddressClass = $customerAddressClass;
        $this->orderContainer = null;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressClass()
    {
        return $this->customerAddressClass;
    }

    /**
     * @return mixed
     */
    public function getCustomerClass()
    {
        return $this->customerClass;
    }

    /**
     * @return mixed
     */
    public function getOrderClass()
    {
        return $this->orderClass;
    }

    /**
     * @return mixed
     */
    public function getOrderItemClass()
    {
        return $this->orderItemClass;
    }

    /**
     * @return mixed
     */
    public function getProductClass()
    {
        return $this->productClass;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getOrderContainer() {
        if (!$this->orderContainer) {
            $connection = $this->container->get('doctrine.dbal.default_connection');
            /** @var \PDO $pdo */
            $pdo = $connection->getWrappedConnection();

            $this->orderContainer = $this->container->get('session')->get('orderContainer');
            if (!$this->orderContainer || $this->orderContainer->getPayStatus() != static::STATUS_UNPAID) {
                $this->orderContainer = new $this->orderClass($pdo);
                $this->container->get('session')->set('orderContainer', $this->orderContainer);
            }

            //convert 1/0 to boolean
            $this->orderContainer->setBillingSame($this->orderContainer->getBillingSame() ? true : false);
            $this->orderContainer->setBillingSave($this->orderContainer->getBillingSave() ? true : false);
            $this->orderContainer->setShippingSave($this->orderContainer->getShippingSave() ? true : false);

            //attach customer email to order
            /** @var TokenStorage $tokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');
            $customer = $tokenStorage->getToken()->getUser();
            if (!$this->orderContainer->getEmail() && gettype($customer) == 'object') {
                $this->orderContainer->setEmail($customer->getTitle());
            }

            //sync order items
            foreach ($this->orderContainer->getOrderItems() as $orderItem) {
                $exist = false;
                foreach ($this->orderContainer->getPendingItems() as $pendingItem) {
                    if ($pendingItem->getUniqid() == $orderItem->getUniqid()) {
                        $exist = true;
                    }
                }
                if (!$exist) {
                    $this->orderContainer->addPendingItem($orderItem);
                }
            }
        }

        return $this->orderContainer;
    }

    /**
     * @param $field
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public function getOrderContainerFromDb($field, $value) {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $orderContainer = $this->orderClass::active($pdo, array(
            'whereSql' => "m.$field = ?",
            'params' => [$value],
            'oneOrNull' => 1,
        ));
        if ($orderContainer) {
            $this->orderContainer = $orderContainer;
            $this->container->get('session')->set('orderContainer', $this->orderContainer);
        }
        return $orderContainer;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getProductById($id) {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        return $this->productClass::getById($pdo, $id);
    }


    public function addOrderItem($productId, $productQty) {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        if (!$this->orderContainer) {
            $this->orderContainer = $this->getOrderContainer();
        }

        $product = $this->getProductById($productId);
        if ($product) {
            $exist = false;
            $pendingItems = $this->orderContainer->getPendingItems();
            foreach ($pendingItems as $pendingItem) {
                if ($pendingItem->getProductId() == $productId) {
                    $pendingItem->setQuantity($pendingItem->getQuantity() + $productQty);
                    $pendingItem->setSubtotal($product->getPrice() * $pendingItem->getQuantity());
                    $exist = true;
                    break;
                }
            }

            if (!$exist) {
                $orderItem = new $this->orderItemClass($pdo);
                $orderItem->setTitle(($product->getVariantProduct() ? $product->getParentProductId() . ' - ' : '') . $product->getTitle());
                $orderItem->setOrderId($this->orderContainer->getUniqid());
                $orderItem->setProductId($productId);
                $orderItem->setPrice($product->getPrice());
                $orderItem->setQuantity($productQty);
                $orderItem->setSubtotal($product->getPrice() * $orderItem->getQuantity());
                $orderItem->setWeight($product->getWeight());
                $orderItem->setTotalWeight($product->getWeight() * $productQty);
                $this->orderContainer->addPendingItem($orderItem);
            }
            $this->orderContainer->update();
        }
        return $this->orderContainer;
    }

    /**
     * @return int
     */
    public function DELIVERY_HIDDEN() {
        return static::DELIVERY_HIDDEN;
    }

    /**
     * @return int
     */
    public function DELIVERY_VISIBLE() {
        return static::DELIVERY_VISIBLE;
    }
}