<?php

namespace Pz\Controller;

use Pz\Orm\Customer;
use Pz\Orm\CustomerAddress;
use Pz\Orm\Order;
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

require_once (__DIR__ . '/../../PxPay_Curl.inc.php');

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

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Cart');
        return $this->render('pz/cart/cart.html.twig', array(
            'node' => $page,
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

        /** @var Order $orderContainer */
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
                /** @var Customer $customer */
                $customer = $this->container->get('security.token_storage')->getToken()->getUser();
                if ($customer && gettype($customer) == 'object') {
                    $orderContainer->setCustomerId($customer->getId());

                    if ($orderContainer->getBillingSave()) {
                        $customerAddress = new CustomerAddress($pdo);
                        $customerAddress->setTitle('');
                        $customerAddress->setCustomerId($customer->getId());
                        $customerAddress->setAddress($orderContainer->getBillingAddress());
                        $customerAddress->setAddress2($orderContainer->getBillingAddress2());
                        $customerAddress->setState($orderContainer->getBillingState());
                        $customerAddress->setCity($orderContainer->getBillingCity());
                        $customerAddress->setPostcode($orderContainer->getBillingPostcode());
                        $customerAddress->setCountry($orderContainer->getBillingCountry());
                        $customerAddress->setFirstname($orderContainer->getBillingFirstname());
                        $customerAddress->setLastname($orderContainer->getBillingLastname());
                        $customerAddress->setPhone($orderContainer->getBillingPhone());
                        $customerAddress->setPrimaryAddress(count($customer->objAddresses()) ? 0 : 1);
                        $customerAddress->save();
                    }

                    if ($orderContainer->getShippingSave()) {
                        $customerAddress = new CustomerAddress($pdo);
                        $customerAddress->setTitle('');
                        $customerAddress->setCustomerId($customer->getId());
                        $customerAddress->setAddress($orderContainer->getShippingAddress());
                        $customerAddress->setAddress2($orderContainer->getShippingAddress2());
                        $customerAddress->setState($orderContainer->getShippingState());
                        $customerAddress->setCity($orderContainer->getShippingCity());
                        $customerAddress->setPostcode($orderContainer->getShippingPostcode());
                        $customerAddress->setCountry($orderContainer->getShippingCountry());
                        $customerAddress->setFirstname($orderContainer->getShippingFirstname());
                        $customerAddress->setLastname($orderContainer->getShippingLastname());
                        $customerAddress->setPhone($orderContainer->getShippingPhone());
                        $customerAddress->setPrimaryAddress(count($customer->objAddresses()) ? 0 : 1);
                        $customerAddress->save();
                    }
                }

                //ORDER: Save order items
                $orderItems = array();
                foreach ($orderContainer->getPendingItems() as $itm) {
                    $itm->save();
                    $orderItems[] = $itm;
                }
                $orderContainer->setOrderItems($orderItems);
                $orderContainer->save();

                if ($data['action'] == 'paypal') {
                    $gateway = static::getPaypalGateway();
                    $params = static::getPaypalParams($orderContainer);
                    $response = $gateway->purchase($params)->send();
                    if ($response->isSuccessful()) {
                        print_r($response);
                        exit;
                    } elseif ($response->isRedirect()) {
                        $orderContainer->setPayStatus(CartService::STATUS_SUBMITTED);
                        $orderContainer->setPayRequest(json_encode($response->getData()));
                        $orderContainer->setPayToken($response->getTransactionReference());
                        $orderContainer->setPayDate(date('Y-m-d H:i:s'));
                        $orderContainer->save();
                        $response->redirect();
                    }

                } elseif ($data['action'] == 'dps') {
                    $baseUrl = $request->getScheme() . '://' . $request->getHost() . '/cart';
                    $uniqid = $orderContainer->getId() . "_" . substr(time() . "", -5);

                    $request = new \PxPayRequest();
                    $request->setEnableAddBillCard(1) ;
                    $request->setAmountInput($orderContainer->getTotal()) ;
                    $request->setTxnData1($uniqid);# whatever you want to appear
                    $request->setTxnType('Purchase');
                    $request->setCurrencyInput('NZD');
                    $request->setBillingId($orderContainer->getId());
                    $request->setMerchantReference($uniqid); # fill this with your order number
                    $request->setEmailAddress($orderContainer->getEmail());
                    $request->setUrlFail($baseUrl . '-dps-fail');
                    $request->setUrlSuccess($baseUrl . '-dps-finalise');
                    $request->setTxnId($uniqid);
                    $pxaccess = $this->getDpsGateway();
                    $requestString = $pxaccess->makeRequest($request);

                    $orderContainer->setPayStatus(CartService::STATUS_SUBMITTED);
                    $orderContainer->setPayRequest(print_r($request, true));
                    $orderContainer->setPayToken($uniqid);
                    $orderContainer->setPayDate(date('Y-m-d H:i:s'));
                    $orderContainer->save();

                    $pxaccess = null;
                    $pxXml = $request->toXml();
                    $response = new \MifMessage($requestString);
                    $dpsUrl = $response->get_element_text("URI");
                    $valid = $response->get_attribute("valid");

                    if ($valid) {
                        return new RedirectResponse($dpsUrl);
                    }
                }
            } else {
                return new RedirectResponse('/cart');
            }
        }

        $form = $form->createView();

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Cart');
        return $this->render('pz/cart/cart-review.html.twig', array(
            'node' =>$page,
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

//        $messageBody = $this->container->get('twig')->render("pz/cart/email/invoice.twig", array(
//            'orderContainer' => $orderContainer,
//        ));
//
//        $message = (new \Swift_Message())
//            ->setSubject('Invoice #' . $orderContainer->getUniqid())
//            ->setFrom(array(getenv('EMAIL_FROM')))
//            ->setTo(array($orderContainer->getEmail()))
//            ->setBcc(array(getenv('EMAIL_BCC')))
//            ->setBody($messageBody, 'text/html');
//        $this->container->get('mailer')->send($message);

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Cart');
        return $this->render('pz/cart/cart-success.html.twig', array(
            'node' => $page,
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

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Cart');
        return $this->render('pz/cart/cart-failed.html.twig', array(
            'node' => $page,
            'orderContainer' => $orderContainer,
        ));
    }

    /**
     * @route("/cart-paypal-cancel")
     * @return Response
     */
    public function paypalCancelOrder()
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
     * @route("/cart-paypal-finalise")
     * @return Response
     */
    public function paypalFinaliseOrder()
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

                $messageBody = $this->container->get('twig')->render("pz/cart/email/invoice.twig", array(
                    'orderContainer' => $orderContainer,
                ));
                $orderContainer->setEmailContent($messageBody);
                $orderContainer->save();

                $message = (new \Swift_Message())
                    ->setSubject('Invoice #' . $orderContainer->getUniqid())
                    ->setFrom(array(getenv('EMAIL_FROM')))
                    ->setTo(array($orderContainer->getEmail()))
                    ->setBcc(array(getenv('EMAIL_BCC')))
                    ->setBody($messageBody, 'text/html');
                $this->container->get('mailer')->send($message);

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
     * @route("/cart-dps-finalise")
     * @return Response
     */
    public function dpsFinaliseOrder()
    {
        $request = Request::createFromGlobals();

        $pxaccess = $this->getDpsGateway();
        $pxResponse = $pxaccess->getResponse($request->get('result'));

        $orderContainer = $this->cartService->getOrderContainerFromDb('payToken', $pxResponse->getTxnData1());
        if (!$orderContainer) {
            throw new NotFoundHttpException();
        }

        if ($orderContainer->getPayStatus() != CartService::STATUS_SUCCESS) {

            $orderContainer->setPayStatus($pxResponse->getSuccess() ? CartService::STATUS_SUCCESS : CartService::STATUS_UNPAID);
            $orderContainer->setPayResponse(print_r($pxResponse, true));
            $orderContainer->save();

            if ($orderContainer->getPayStatus() == CartService::STATUS_SUCCESS) {

                $messageBody = $this->container->get('twig')->render("pz/cart/email/invoice.twig", array(
                    'orderContainer' => $orderContainer,
                ));
                $orderContainer->setEmailContent($messageBody);
                $orderContainer->save();

                $message = (new \Swift_Message())
                    ->setSubject('Invoice #' . $orderContainer->getUniqid())
                    ->setFrom(array(getenv('EMAIL_FROM')))
                    ->setTo(array($orderContainer->getEmail()))
                    ->setBcc(array(getenv('EMAIL_BCC'), getenv('EMAIL_BCC_ORDER')))
                    ->setBody($messageBody, 'text/html');
                $this->container->get('mailer')->send($message);

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
    }

    /**
     * @return \PxPay_Curl
     */
    public function getDpsGateway(){
        return new \PxPay_Curl(getenv('PX_ACCESS_URL'), getenv('PX_ACCESS_USERID'), getenv('PX_ACCESS_KEY'));;
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
            'returnUrl' => "$baseUrl-paypal-finalise",
            'cancelUrl' => "$baseUrl-paypal-cancel",
            'card' => $card,
        );

        return $params;
    }
}