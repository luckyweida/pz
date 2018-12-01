<?php

namespace Pz\Form\Handler;

use Cocur\Slugify\Slugify;
use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Pz\Axiom\Eve;
use Pz\Axiom\Walle;
use Pz\Orm\_Model;
use Pz\Orm\DataGroup;
use Pz\Redirect\RedirectException;
use Pz\Service\Db;
use Pz\Service\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class CartHandler
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle($orm)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\Cart::class, $orm);

        $shop = new Shop($this->container);
        $orderContainer = $shop->getOrderContainer();

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->get($form->getName());
            if ($data['action'] == 'paypal') {
                $apiContext = $this->getApiContext(getenv('PAYPAL_CLIENT_ID'), getenv('PAYPAL_CLIENT_SECRET'));


                $billingAddress = new Address();
                if ($orderContainer->getBillingSame()) {
                    $billingAddress->setLine1($orderContainer->getShippingAddress());
                    $billingAddress->setLine2($orderContainer->getShippingAddress2());
                    $billingAddress->setCity($orderContainer->getShippingCity());
                    $billingAddress->setPostalCode($orderContainer->getShippingPostcode());
                } else {
                    $billingAddress->setLine1($orderContainer->getBillingAddress());
                    $billingAddress->setLine2($orderContainer->getBillingAddress2());
                    $billingAddress->setCity($orderContainer->getBillingCity());
                    $billingAddress->setPostalCode($orderContainer->getBillingPostcode());
                }

                $billingAddress->setCountryCode('NZ');

                $payerInfo = new PayerInfo();
                $payerInfo->setEmail($orderContainer->getEmail());
                $payerInfo->setBillingAddress($billingAddress);

                $payer = new Payer();
                $payer->setPaymentMethod("paypal");
                $payer->setPayerInfo($payerInfo);

                $amount = new Amount();
                $amount->setCurrency("NZD")
                    ->setTotal(0.01);

                $transaction = new Transaction();
                $transaction->setAmount($amount)
                    ->setDescription("Online Shoping Payment")
                    ->setInvoiceNumber($orderContainer->getUniqid());

                $baseUrl = $request->getScheme() . '://' . $request->getHost() . '/cart';
                $redirectUrls = new RedirectUrls();
                $redirectUrls->setReturnUrl("$baseUrl/finalise")
                    ->setCancelUrl("$baseUrl/cancel");

                $payment = new Payment();
                $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrls)
                    ->setTransactions(array($transaction));

                $request = clone $payment;
                try {
                    $payment->create($apiContext);
                } catch (\Exception $ex) {
                    while (@ob_end_clean());
                    var_dump($ex);exit;
                }


                $approvalUrl = $payment->getApprovalLink();
                throw new RedirectException($approvalUrl, 301);

            } else {
                throw new RedirectException('/cart-review', 301);
            }
        } else if ($form->isSubmitted() && $request->getPathInfo() != '/cart') {
            throw new RedirectException('/cart', 301);
        }

        return $form->createView();
    }

    function getApiContext($clientId, $clientSecret)
    {
        // #### SDK configuration
        // Register the sdk_config.ini file in current directory
        // as the configuration source.
        /*
        if(!defined("PP_CONFIG_PATH")) {
            define("PP_CONFIG_PATH", __DIR__);
        }
        */
        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $clientId,
                $clientSecret
            )
        );
        // Comment this line out and uncomment the PP_CONFIG_PATH
        // 'define' block if you want to use static file
        // based configuration
        $apiContext->setConfig(
            array(
                'mode' => 'live',
                'log.LogEnabled' => true,
                'log.FileName' => './PayPal.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
                //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );
        // Partner Attribution Id
        // Use this header if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution.
        // To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal
        // $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', '123123123');
        return $apiContext;
    }

}