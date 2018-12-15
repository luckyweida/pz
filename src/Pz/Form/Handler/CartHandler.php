<?php

namespace Pz\Form\Handler;

use Pz\Orm\Order;
use Pz\Redirect\RedirectException;
use Web\Service\Shop;

use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class CartHandler
{
    private $container;

    /**
     * CartHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Order $orderContainer
     * @return \Symfony\Component\Form\FormView
     * @throws RedirectException
     */
    public function handle(Order $orderContainer)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $orderContainer->setBillingSame($orderContainer->getBillingSame() ? true : false);
        $orderContainer->setBillingSave($orderContainer->getBillingSave() ? true : false);
        $orderContainer->setShippingSave($orderContainer->getShippingSave() ? true : false);

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\Cart::class, $orderContainer, array(
            'container' => $this->container,
        ));

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            Shop::updateOrder($orderContainer, $pdo);

            if ($orderContainer->getId()) {
                /** @var Order $oc */
                $oc = Order::getById($pdo, $orderContainer->getId());
                if ($oc->getPayStatus() == Order::STATUS_SUCCESS) {
                    $this->container->get('session')->set('orderContainer', null);
                    throw new RedirectException('/cart-success?id=' . $orderContainer->getUniqid(), 301);
                }
            }

            $data = $request->get($form->getName());
            if ($data['action'] == 'paypal') {
                $gateway = static::getPaypalGateway();
                $params = static::getPaypalParams($orderContainer);

                $response = $gateway->purchase($params)->send();

                $orderContainer->setPayStatus(Order::STATUS_SUBMITTED);
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


            } else {
                throw new RedirectException('/cart-review', 301);
            }
        } else if ($form->isSubmitted() && $request->getPathInfo() != '/cart') {
            throw new RedirectException('/cart', 301);
        }

        return $form->createView();
    }

    /**
     * @return \Omnipay\Common\GatewayInterface
     */
    static function getPaypalGateway()
    {
        $factory = new GatewayFactory();
        $gateway = $factory->create('PayPal_Express');
        $gateway->setUsername('ns.gresource_api1.gmail.com');
        $gateway->setPassword('T7ZDTGU6KN3ZVHQL');
        $gateway->setSignature('AlHocZw.D-4wIMlpxjF1YGncCpfIAicPkwVrE4CMz47JMRS0lm9rg57f');
        $gateway->setTestMode(false);
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
            'billingPhone' => $orderContainer->getBillingPhone(),
            'billingCity' => $orderContainer->getBillingCity(),
            'billingState' => '',
            'billingPostCode' => $orderContainer->getBillingPostcode(),
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