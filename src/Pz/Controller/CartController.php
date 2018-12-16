<?php

namespace Pz\Controller;

use Pz\Orm\Order;
use Pz\Orm\OrderItem;
use Pz\Orm\Product;
use Pz\Orm\PromoCode;
use Web\Service\CartService;

use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;

use Http\Discovery\Exception\NotFoundException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends Controller
{
    /**
     * @route("/cart")
     * @return Response
     */
    public function cart()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $cart = new CartService($this->container);
        $orderContainer = $cart->getOrderContainer();

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\Cart::class, $orderContainer, array(
            'container' => $this->container,
        ));

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($orderContainer->getId()) {
                    /** @var Order $oc */
                    $oc = Order::getById($pdo, $orderContainer->getId());
                    if ($oc->getPayStatus() == CartService::STATUS_SUCCESS()) {
                        $this->container->get('session')->set('orderContainer', null);
                        return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
                    }
                }
                CartService::updateOrder($orderContainer, $pdo);

                return new RedirectResponse('/cart-review');
            }
        }

        $form = $form->createView();

        return $this->render('cart.html.twig', array(
            'orderContainer' => $orderContainer,
            'form' => $form,
        ));
    }

    /**
     * @route("/cart-review")
     * @return Response
     */
    public function cartReview()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $cart = new CartService($this->container);
        $orderContainer = $cart->getOrderContainer();

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\Cart::class, $orderContainer, array(
            'container' => $this->container,
        ));

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($orderContainer->getId()) {
                    /** @var Order $oc */
                    $oc = Order::getById($pdo, $orderContainer->getId());
                    if ($oc->getPayStatus() == CartService::STATUS_SUCCESS()) {
                        $this->container->get('session')->set('orderContainer', null);
                        return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
                    }
                }
                CartService::updateOrder($orderContainer, $pdo);

                $data = $request->get($form->getName());
                if ($data['action'] == 'paypal') {
                    $gateway = static::getPaypalGateway();
                    $params = static::getPaypalParams($orderContainer);

                    $response = $gateway->purchase($params)->send();

                    $orderContainer->setPayStatus(CartService::STATUS_SUBMITTED());
                    $orderContainer->setPayRequest(json_encode($response->getData()));
                    $orderContainer->setPayToken($response->getTransactionReference());
                    $orderContainer->setPayDate(date('Y-m-d H:i:s'));
                    $orderContainer->save();

                    //ORDER: Save order items
                    $orderItems = array();
                    foreach ($orderContainer->getPendingItems() as $itm) {
                        $itm->save();
                        $orderItems[] = $itm;
                    }
                    $orderContainer->setOrderItems($orderItems);

                    if ($response->isSuccessful()) {
                        print_r($response);
                        exit;
                    } elseif ($response->isRedirect()) {
                        $response->redirect();
                    }
                }
            } else {
                return new RedirectResponse('/cart');
            }
        }

        $form = $form->createView();

        return $this->render('cart-review.html.twig', array(
            'orderContainer' => $orderContainer,
            'form' => $form,
        ));
    }

    /**
     * @route("/cart-success")
     * @return Response
     */
    public function cartSuccess()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $id = $request->get('id');
        /** @var Order $orderContainer */
        $orderContainer = Order::getByField($pdo, 'uniqid', $id);
        if (!$orderContainer || $orderContainer->getPayStatus() != CartService::STATUS_SUCCESS()) {
            throw new NotFoundException();
        }

        return $this->render('cart-success.html.twig', array(
            'orderContainer' => $orderContainer,
        ));
    }

    /**
     * @route("/cart-failed")
     * @return Response
     */
    public function cartFailed()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $id = $request->get('id');
        /** @var Order $orderContainer */
        $orderContainer = Order::getByField($pdo, 'uniqid', $id);
        if (!$orderContainer || $orderContainer->getPayStatus() == CartService::STATUS_SUCCESS()) {
            throw new NotFoundException();
        }

        return $this->render('cart-failed.html.twig', array(
            'orderContainer' => $orderContainer,
        ));
    }

    /**
     * @route("/cart-cancel")
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

        if ($orderContainer->getPayStatus() != CartService::STATUS_SUCCESS()) {
            $orderContainer->setPayStatus(CartService::STATUS_UNPAID());
        }
        $this->container->get('session')->set('orderContainer', $orderContainer);
        return new RedirectResponse('/cart');
    }

    /**
     * @route("/cart-finalise")
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

        if ($orderContainer->getPayStatus() != CartService::STATUS_SUCCESS()) {

            $params = static::getPaypalParams($orderContainer);
            $gateway = static::getPaypalGateway();
            $response = $gateway->completePurchase($params)->send();
            $paypalResponse = $response->getData();

            $orderContainer->setPayResponse(json_encode($paypalResponse));

            if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success') {
                $orderContainer->setPayStatus(CartService::STATUS_SUCCESS());
                $orderContainer->save();

//                $messageBody = $this->container->get('twig')->render("email/invoice.twig", array(
//                    'orderContainer' => $orderContainer,
//                ));
                $messageBody = '';
                $orderContainer->setEmailContent($messageBody);

//                var_dump($this->container);
//                exit;

//                $message = \Swift_Message::newInstance()
//                    ->setSubject('TradeKiwi Invoice #' . $orderContainer->getUniqid())
//                    ->setFrom(array(EMAIL_FROM))
//                    ->setTo(array($orderContainer->getEmail()))
//                    ->setBcc(array(EMAIL_BCC))
//                    ->setBody(
//                        $messageBody, 'text/html'
//                    );
//                $app['mailer']->send($message);

                $this->container->get('session')->set('orderContainer', null);
                return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());

            } else {
//                $template = 'payment-failed.twig';
                $orderContainer->setPayStatus(CartService::STATUS_UNPAID());
                $orderContainer->save();
                $this->container->get('session')->set('orderContainer', $orderContainer);
                return new RedirectResponse('/cart-failed?id=' . $orderContainer->getUniqid());
            }

        } else if ($orderContainer->getPayStatus() == CartService::STATUS_SUCCESS()) {
            $this->container->get('session')->set('orderContainer', null);
            return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
        }


        throw new NotFoundException();
    }

    /**
     * @route("/login")
     * @return Response
     */
    public function member_login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @route("/member/after_login")
     * @return Response
     */
    public function member_after_login()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();


        $redirectUrl = '/member/dashboard';
        $cart = new CartService($this->container);
        $orderContainer = $cart->getOrderContainer();
        if (count($orderContainer->getPendingItems())) {
            $redirectUrl = '/cart';
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @route("/activate/{id}")
     * @return Response
     */
    public function activate($id)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        /** @var Customer $customer */
        $customer = Customer::getByField($pdo, 'uniqid', $id);
        if (!$customer) {
            throw new NotFoundException();
        }

        if ($customer->getIsActivated() == 1) {
            throw new NotFoundException();
        }

        $customer->setIsActivated(1);
        $customer->setStatus(1);
        $customer->save();

        $data = Customer::data($pdo, array(
            'whereSql' => 'm.title = ? AND m.id != ? AND m.status = 0',
            'params' => array($customer->getTitle(), $customer->getId()),
        ));

        foreach ($data as $itm) {
            $itm->delete();
        }


        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordToken($customer, $customer->getPassword(), "public", $customer->getRoles());
        $tokenStorage->setToken($token);
        $this->get('session')->set('_security_member', serialize($token));
        return new RedirectResponse('\member\dashboard');
    }

    /**
     * @route("/reset/{token}")
     * @return Response
     */
    public function resetPassword($token)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        /** @var Customer $customer */
        $customer = Customer::getByField($pdo, 'resetToken', $token);
        if (!$customer) {
            throw new NotFoundException();
        }

        if (time() >= strtotime($customer->getResetExpiry())) {
            throw new NotFoundException();
        }

        $customer->setResetToken('');
//        $customer->save();

        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordToken($customer, $customer->getPassword(), "public", $customer->getRoles());
        $tokenStorage->setToken($token);
        $this->get('session')->set('_security_member', serialize($token));
        return new RedirectResponse('\member\password');
    }

    /**
     * @route("/xhr/cart/item/add/{id}/{quantity}")
     * @return Response
     */
    public function xhrAddOrderItem($id, $quantity)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $cart = new CartService($this->container);
        $orderContainer = $cart->getOrderContainer();

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
                $orderItem->setWeight($product->getWeight());
                $orderItem->setTotalWeight($product->getWeight() * $quantity);
                $orderContainer->addPendingItem($orderItem);
            }
        }

        CartService::updateOrder($orderContainer, $pdo);

        return new Response(count($orderContainer->getPendingItems()));
    }

    /**
     * @route("/xhr/cart/order")
     * @return Response
     */
    public function xhrGetOrder()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new CartService($this->container);
        $orderContainer = $shop->getOrderContainer();

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/xhr/cart/item/qty")
     * @return Response
     */
    public function xhrChangeItemQty()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new CartService($this->container);
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
                $pendingItem->setTotalWeight($pendingItem->getWeight() * $pendingItem->getQuantity());
            }
        }

        CartService::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/xhr/cart/order/address/update")
     * @return Response
     */
    public function xhrUpdateAddress()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new CartService($this->container);
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

        CartService::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/xhr/cart/order/delivery/update")
     * @return Response
     */
    public function xhrUpdateDeliveryOption()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new CartService($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $deliverOptionId = json_decode($request->get('id'));

        $orderContainer->setDeliveryOptionId($deliverOptionId);

        CartService::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/xhr/cart/item/delete")
     * @return Response
     */
    public function xhrDeleteItem()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new CartService($this->container);
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

        CartService::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @route("/xhr/cart/promo/apply")
     * @return Response
     */
    public function xhrApplyPromoCode()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $shop = new CartService($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $code = $request->get('code');

        $orderContainer->setPromoCode($code);

        CartService::updateOrder($orderContainer, $pdo);

        return new JsonResponse($orderContainer);
    }

    /**
     * @return \Omnipay\Common\GatewayInterface
     */
    static function getPaypalGateway()
    {
        $factory = new GatewayFactory();
        $gateway = $factory->create('PayPal_Express');
        $gateway->setUsername(getenv('PAYPAL_USERNAME'));
        $gateway->setPassword(getenv('PAYPAL_PASSWORD'));
        $gateway->setSignature(getenv('PAYPAL_SIGNATURE'));
        $gateway->setTestMode(getenv('PAYPAL_TESTMODE'));
        return $gateway;
    }

    /**
     * @param Order $orderContainer
     * @return array
     */
    static function getPaypalParams(Order $orderContainer)
    {
        $cardInput = array(
            'firstName' => $orderContainer->getBillingFirstname(),
            'lastName' => $orderContainer->getBillingLastname(),
            'billingAddress1' => $orderContainer->getBillingAddress(),
            'billingAddress2' => $orderContainer->getBillingAddress2(),
            'billingPhone' => '+64' . ltrim($orderContainer->getBillingPhone(), '0'),
            'billingCity' => $orderContainer->getBillingCity(),
            'billingState' => '',
            'billingPostCode' => $orderContainer->getBillingPostcode(),
            'billingCountry' => $orderContainer->getBillingCountry(),
            'email' => $orderContainer->getEmail(),
        );
        $card = new CreditCard($cardInput);

        $request = Request::createFromGlobals();
        $baseUrl = $request->getScheme() . '://' . $request->getHost() . '/cart';
        $params = array(
            'amount' => (float)$orderContainer->getTotal(),
            'currency' => 'NZD',
            'description' => 'Online Shopping Payment',
            'transactionId' => $orderContainer->getUniqid(),
            'transactionReference' => $orderContainer->getBillingFirstname() . ' ' . $orderContainer->getBillingLastname(),
            'returnUrl' => "$baseUrl-finalise",
            'cancelUrl' => "$baseUrl-cancel",
            'card' => $card,
        );

        return $params;
    }
}