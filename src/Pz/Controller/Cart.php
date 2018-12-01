<?php

namespace Pz\Controller;


use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\Product;
use Pz\Orm\PromoCode;
use Pz\Service\Shop;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Cart extends Controller
{
    /**
     * @route("/cart/item/add/{id}/{quantity}", name="addOrderItem")
     * @return Response
     */
    public function addOrderItem($id, $quantity)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        $exist = false;
        /** @var OrderItem[] $pendingItems */
        $pendingItems = $orderContainer->getPendingItems();
        foreach ($pendingItems as $pendingItem) {
            if ($pendingItem->getProductId() == $id) {
                $product = Product::getById($pdo, $id);
                if ($product) {
                    $pendingItem->setQuantity($pendingItem->getQuantity() + $quantity);
                    $pendingItem->setSubtotal($product->getPrice() * $pendingItem->getQuantity());
                    $exist = true;
                    break;
                }
            }
        }

        if (!$exist) {
            /** @var Product $product */
            $product = Product::getById($pdo, $id);
            if ($product) {
                $orderItem = new OrderItem($pdo);
                $orderItem->setTitle(($product->getVariantProduct() ? $product->getParentProductId() . ' - ' : '') . $product->getTitle());
                $orderItem->setOrderId($orderContainer->getUniqid());
                $orderItem->setProductId($id);
                $orderItem->setPrice($product->getPrice());
                $orderItem->setQuantity($quantity);
                $orderItem->setSubtotal($product->getPrice() * $orderItem->getQuantity());

                $orderContainer->addPendingItem($orderItem);
            }
        }

        static::updateOrder($orderContainer, $pdo);

        return new Response(count($orderContainer->getPendingItems()));
    }

    /**
     * @route("/cart/order", name="getOrder")
     * @return Response
     */
    public function getOrder()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/cart/item/qty", name="changeItemQty")
     * @return Response
     */
    public function changeItemQty()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $id = $request->get('id');
        $qty = $request->get('qty');

        /** @var OrderItem[] $pendingItems */
        $pendingItems = $orderContainer->getPendingItems();
        foreach ($pendingItems as $pendingItem) {
            if ($pendingItem->getUniqid() == $id) {
                $pendingItem->setQuantity($qty);
                $pendingItem->setSubtotal($pendingItem->getPrice() * $pendingItem->getQuantity());
            }
        }

        static::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/cart/item/delete", name="deleteItem")
     * @return Response
     */
    public function deleteItem()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $id = $request->get('id');

        /** @var OrderItem[] $pendingItems */
        $pendingItems = $orderContainer->getPendingItems();
        foreach ($pendingItems as $idx => $pendingItem) {
            if ($pendingItem->getUniqid() == $id) {
                $pendingItems = array_splice($pendingItems, $idx, 0);
                $orderContainer->setPendingItems($pendingItems);
            }
        }

        static::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/cart/promo/apply", name="applyPromoCode")
     * @return Response
     */
    public function applyPromoCode()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $code = $request->get('code');

        $orderContainer->setPromoCode($code);

        static::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

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

        $gst = round(($subtotal - $discount) * 0.15, 2);


        $total = $subtotal - $discount + $gst;

        $orderContainer->setDiscount($discount);
        $orderContainer->setSubtotal($subtotal);
        $orderContainer->setGst($gst);
        $orderContainer->setTotal($total);

    }
}