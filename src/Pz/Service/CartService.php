<?php

namespace Pz\Service;

use Pz\Orm\Customer;
use Pz\Orm\DeliveryOption;
use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\Product;
use Pz\Orm\ProductCategory;
use Pz\Orm\PromoCode;

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

        $orderContainer->setBillingSame($orderContainer->getBillingSame() ? true : false);
        $orderContainer->setBillingSave($orderContainer->getBillingSave() ? true : false);
        $orderContainer->setShippingSave($orderContainer->getShippingSave() ? true : false);

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
     * @param Order $orderContainer
     * @param \PDO $pdo
     */
    static public function updateOrder(Order &$orderContainer, \PDO $pdo)
    {
        $result = 0;

        /** @var OrderItem[] $pendingItems */
        $pendingItems = $orderContainer->getPendingItems();
        foreach ($pendingItems as $pendingItem) {
            $result += $pendingItem->getSubtotal();
        }

        $subtotal = round($result * 20 / 23, 2);


        $discount = 0;
        /** @var PromoCode $promoCode */
        $promoCode = PromoCode::getByField($pdo, 'title', $orderContainer->getPromoCode());
        if ($promoCode) {
            $valid = true;
            if ($promoCode->getStartdate() && strtotime($promoCode->getStartdate()) >= time()) {
                $valid = false;
            }
            if ($promoCode->getEnddate() && strtotime($promoCode->getEnddate()) <= time()) {
                $valid = false;
            }

            if ($valid) {
                if ($promoCode->getPerc() == 1) {
                    $discount = round(($promoCode->getValue() / 100) * $subtotal, 2);
                } else {
                    $discount = $promoCode->getValue();
                }
            }
        }

        $afterDiscount = $subtotal - $discount;
        $gst = round($afterDiscount * 0.15, 2);


        $deliveryFee = 0;

        $countryCode = $orderContainer->getCountryCode();
        $deliveryOptions = $orderContainer->getDeliveryOptions();
        if ($countryCode) {
            /** @var DeliveryOption $firstDeliveryOption */
            $selectedDeliveryOption = $deliveryOptions[0];
            $deliveryOptionId = $orderContainer->getDeliveryOptionId();

            foreach ($deliveryOptions as $deliveryOption) {
                if ($deliveryOption->getId() == $deliveryOptionId) {
                    $selectedDeliveryOption = $deliveryOption;
                }
            }

            $deliveryFee = $selectedDeliveryOption->getPrice();

            $orderContainer->setDeliveryOptionDescription($selectedDeliveryOption->getTitle());
            $orderContainer->setDeliveryOptionId($selectedDeliveryOption->getId());
            $orderContainer->setDeliveryOptionStatus(static::DELIVERY_VISIBLE());
        } else {
            $orderContainer->setDeliveryOptionDescription('');
            $orderContainer->setDeliveryOptionId(null);
            $orderContainer->setDeliveryOptionStatus(static::DELIVERY_HIDDEN());
        }

        $orderContainer->setDeliveryFee($deliveryFee);



        $total = $subtotal - $discount + $gst + max($deliveryFee, 0);

        $orderContainer->setDiscount($discount);
        $orderContainer->setAfterDiscount($afterDiscount);
        $orderContainer->setSubtotal($subtotal);
        $orderContainer->setGst($gst);
        $orderContainer->setTotal($total);

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