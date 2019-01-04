<?php

namespace Pz\Controller;

use Pz\Service\CartService;

use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

trait TraitCart
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

        $orderContainer = $this->cartService->getOrderContainer();

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
                $oc = $this->cartService->getOrderContainerFromDb('uniqid', $orderContainer->getUniqid());
                if ($oc && $oc->getPayStatus() == CartService::STATUS_SUCCESS) {
                    $this->container->get('session')->set('orderContainer', null);
                    return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
                }
                $orderContainer->update();
                return new RedirectResponse('/cart-review');
            }
        }

        $form = $form->createView();

        return $this->render('cart/cart.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Cart',
            ),
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

        $orderContainer = $this->cartService->getOrderContainer();

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
                $oc = $this->cartService->getOrderContainerFromDb('uniqid', $orderContainer->getUniqid());
                if ($oc && $oc->getPayStatus() == CartService::STATUS_SUCCESS) {
                    $this->container->get('session')->set('orderContainer', null);
                    return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
                }
                $orderContainer->update();

                $data = $request->get($form->getName());
                if ($data['action'] == 'paypal') {
                    $gateway = static::getPaypalGateway();
                    $params = static::getPaypalParams($orderContainer);

                    $response = $gateway->purchase($params)->send();

                    if ($response->isSuccessful()) {
                        print_r($response);
                        exit;
                    } elseif ($response->isRedirect()) {
                        //ORDER: Save order items
                        $orderItems = array();
                        foreach ($orderContainer->getPendingItems() as $itm) {
                            $itm->save();
                            $orderItems[] = $itm;
                        }
                        $orderContainer->setOrderItems($orderItems);

                        $orderContainer->setPayStatus(CartService::STATUS_SUBMITTED);
                        $orderContainer->setPayRequest(json_encode($response->getData()));
                        $orderContainer->setPayToken($response->getTransactionReference());
                        $orderContainer->setPayDate(date('Y-m-d H:i:s'));
                        $orderContainer->save();
                        $response->redirect();
                    }
                }
            } else {
                return new RedirectResponse('/cart');
            }
        }

        $form = $form->createView();

        return $this->render('cart/cart-review.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Cart',
            ),
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

        $orderContainer = $this->cartService->getOrderContainerFromDb('uniqid', $id);
        if (!$orderContainer || $orderContainer->getPayStatus() != CartService::STATUS_SUCCESS) {
            throw new NotFoundHttpException();
        }

        return $this->render('cart/cart-success.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Cart',
            ),
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

        $orderContainer = $this->cartService->getOrderContainerFromDb('uniqid', $id);
        if (!$orderContainer || $orderContainer->getPayStatus() == CartService::STATUS_SUCCESS) {
            throw new NotFoundHttpException();
        }

        return $this->render('cart/cart-failed.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Cart',
            ),
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

        $orderContainer = $this->cartService->getOrderContainerFromDb('payToken', $token);
        if (!$orderContainer) {
            throw new NotFoundHttpException();
        }

        if ($orderContainer->getPayStatus() != CartService::STATUS_SUCCESS) {
            $orderContainer->setPayStatus(CartService::STATUS_UNPAID);
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

        $orderContainer = $this->cartService->getOrderContainerFromDb('payToken', $token);
        if (!$orderContainer) {
            throw new NotFoundHttpException();
        }

        if ($orderContainer->getPayStatus() != CartService::STATUS_SUCCESS) {

            $params = static::getPaypalParams($orderContainer);
            $gateway = static::getPaypalGateway();
            $response = $gateway->completePurchase($params)->send();
            $paypalResponse = $response->getData();

            $orderContainer->setPayResponse(json_encode($paypalResponse));

            if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success') {
                $orderContainer->setPayStatus(CartService::STATUS_SUCCESS);
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
                $orderContainer->setPayStatus(CartService::STATUS_UNPAID);
                $orderContainer->save();
                $this->container->get('session')->set('orderContainer', $orderContainer);
                return new RedirectResponse('/cart-failed?id=' . $orderContainer->getUniqid());
            }

        } else if ($orderContainer->getPayStatus() == CartService::STATUS_SUCCESS) {
            $this->container->get('session')->set('orderContainer', null);
            return new RedirectResponse('/cart-success?id=' . $orderContainer->getUniqid());
        }


        throw new NotFoundHttpException();
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
        $gateway->setTestMode(getenv('PAYPAL_TESTMODE') == 'true' ? true : false);
        return $gateway;
    }

    /**
     * @param $orderContainer
     * @return array
     */
    static function getPaypalParams($orderContainer)
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