<?php

namespace Pz\Controller;


use Http\Discovery\Exception\NotFoundException;
use Pz\Form\Handler\CartHandler;
use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\Product;
use Pz\Orm\PromoCode;
use Web\Service\Shop;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Cart extends Controller
{

    /**
     * @route("/cart-cancel", name="cancelOrder")
     * @return Response
     */
    public function cancelOrder()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $token = $request->get('token');
        /** @var Order $orderContainer */
        $orderContainer = Order::getByField($pdo, 'payToken', $token);
        if (!$orderContainer) {
            throw new NotFoundException();
        }

        $this->container->get('session')->set('orderContainer', $orderContainer);
        return new RedirectResponse('/cart');
    }

    /**
     * @route("/cart-finalise", name="finaliseOrder")
     * @return Response
     */
    public function finaliseOrder()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $token = $request->get('token');
        /** @var Order $orderContainer */
        $orderContainer = Order::getByField($pdo, 'payToken', $token);
        if (!$orderContainer) {
            throw new NotFoundException();
        }

        if ($orderContainer->getPayStatus() != 1 || 1) {

            $params = CartHandler::getPaypalParams($orderContainer);
            $gateway = CartHandler::getPaypalGateway();
            $response = $gateway->completePurchase($params)->send();
            $paypalResponse = $response->getData();

            $orderContainer->setPayResponse(json_encode($paypalResponse));

            if(isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success' || 1) {
                $orderContainer->setPayStatus(1);
                $orderContainer->save();

                $messageBody = $this->container->get('twig')->render("email/invoice.twig", array(
                    'orderContainer' => $orderContainer,
                ));
                $orderContainer->setEmailContent($messageBody);

                var_dump($this->container);exit;

                $message = \Swift_Message::newInstance()
                    ->setSubject('TradeKiwi Invoice #' . $orderContainer->getUniqid())
                    ->setFrom(array(EMAIL_FROM))
                    ->setTo(array($orderContainer->getEmail()))
                    ->setBcc(array(EMAIL_BCC))
                    ->setBody(
                        $messageBody,'text/html'
                    );
//                $app['mailer']->send($message);

                $this->container->get('session')->set('orderContainer', null);
                return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());

            } else {
//                $template = 'payment-failed.twig';
                $orderContainer->setPayStatus(0);
                $orderContainer->save();
                return new RedirectResponse('/cart-failed?id=' . $orderContainer->getUniqid());
            }

//

        } else if ($orderContainer->getPayStatus() == 1) {
            $this->container->get('session')->set('orderContainer', null);
            return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
        }


        throw new NotFoundException();
    }


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

        Shop::updateOrder($orderContainer, $pdo);

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

        Shop::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/cart/order/address/update", name="updateAddress")
     * @return Response
     */
    public function updateAddress()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $o = json_decode($request->get('order'));

//        var_dump($o);exit;

        foreach ($o as $idx => $itm) {
            if (strpos($idx, 'shipping') == -1 && strpos($idx, 'billing') == -1) {
                continue;
            }
            $method = 'set' . ucfirst($idx);
            $orderContainer->$method($itm);
        }

        Shop::updateOrder($orderContainer, $pdo);

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
                array_splice($pendingItems, $idx, 1);
                $orderContainer->setPendingItems($pendingItems);
                $pendingItem->delete();
                break;
            }
        }

        Shop::updateOrder($orderContainer, $pdo);

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

        Shop::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

}