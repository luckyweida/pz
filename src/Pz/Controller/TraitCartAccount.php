<?php

namespace Pz\Controller;

use Pz\Orm\Customer;

use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;

use Pz\Orm\CustomerAddress;
use Pz\Orm\Order;
use Pz\Service\CartService;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

trait TraitCartAccount
{
    /**
     * @route("/login")
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Login');
        return $this->render('pz/cart/login.html.twig', array(
            'node' => $page,
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @route("/register")
     * @return Response
     */
    public function register()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $orm = new Customer($pdo);
        $orm->setStatus(0);

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\Register::class, $orm, array(
            'container' => $this->container,
        ));


        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $messageBody = $this->container->get('twig')->render("pz/cart/email/email-activate.twig", array(
                'customer' => $orm,
            ));


            $message = (new \Swift_Message())
                ->setSubject('Acticate your account')
                ->setFrom(array(getenv('EMAIL_FROM')))
                ->setTo($orm->getTitle())
                ->setBody($messageBody, 'text/html');
            $this->container->get('mailer')->send($message);

            $orm->setSource(CartService::CUSTOMER_WEBSITE);
            $orm->save();

            return new RedirectResponse('/activation/required?id=' . $orm->getUniqid());

        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Register');
        return $this->render('pz/cart/register.html.twig', array(
            'node' => $page,
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/activation/required")
     * @return Response
     */
    public function activationRequired()
    {

        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Activation Required');
        return $this->render('pz/cart/confirmation.html.twig', array(
            'node' => $page,
        ));
    }

    /**
     * @route("/forget-password")
     * @return Response
     */
    public function forgetPassword()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();


        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\ForgetPassword::class, null, array(
            'container' => $this->container,
        ));

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $orm = Customer::getByField($pdo, 'title', $data['title']);
            $orm->setResetToken(md5($orm->getUsername() . time() . uniqid()));
            $orm->setResetExpiry(date('Y-m-d H:i:s', strtotime('+24 hours')));
            $orm->save();

            $messageBody = $this->container->get('twig')->render("pz/cart/email/email-forget.twig", array(
                'customer' => $orm,
            ));
            $message = (new \Swift_Message())
                ->setSubject('Reset your password')
                ->setFrom(array(getenv('EMAIL_FROM')))
                ->setTo($orm->getTitle())
                ->setBody($messageBody, 'text/html');
            $this->container->get('mailer')->send($message);

            return new RedirectResponse('/reset-password-email-sent?id=' . $orm->getUniqid());

        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Forget Password');
        return $this->render('pz/cart/forget-password.html.twig', array(
            'node' => $page,
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/reset-password-email-sent")
     * @return Response
     */
    public function resetPasswordEmailSent()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('Reset Password Email Sent');
        return $this->render('pz/cart/confirmation.html.twig', array(
            'node' => $page,
        ));
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
            throw new NotFoundHttpException();
        }

        if ($customer->getIsActivated() == 1) {
            throw new NotFoundHttpException();
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
        $this->get('session')->set('_security_account', serialize($token));
        return new RedirectResponse('\account\dashboard');
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
            throw new NotFoundHttpException();
        }

        if (time() >= strtotime($customer->getResetExpiry())) {
            throw new NotFoundHttpException();
        }

        $customer->setResetToken('');
        $customer->save();

        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordToken($customer, $customer->getPassword(), "public", $customer->getRoles());
        $tokenStorage->setToken($token);
        $this->get('session')->set('_security_account', serialize($token));
        return new RedirectResponse('\account\password');
    }

    /**
     * @route("/account/after_login")
     * @return Response
     */
    public function accountAfterLogin()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $redirectUrl = '/account/dashboard';
        $orderContainer = $this->cartService->getOrderContainer();
        if (count($orderContainer->getPendingItems())) {
            $redirectUrl = '/cart';
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @route("/account/dashboard")
     * @return Response
     */
    public function accountDashboard()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $pagination = $request->get('pagination') ?: 1;
        $limit = 5;

        $orders = $this->cartService->getOrderClass()::active($pdo, array(
            'whereSql' => 'm.customerId = ? AND m.payStatus = ?',
            'params' => array($customer->getId(), CartService::STATUS_SUCCESS),
            'sort' => 'm.id',
            'order' => 'DESC',
            'page' => $pagination,
            'limit' => $limit,
        ));

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\ResendReceipt::class, null, array(
            'orderId' => $request->get('resend_receipt')['orderId'],
            'orderClass' => $this->cartService->getOrderClass(),
            'container' => $this->container,
        ));

        $submitted = 0;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = 1;

            $data = $form->getData();
            $orderContainer = $this->cartService->getOrderClass()::getByField($pdo, 'uniqid', $data['orderId']);
            $orderContainer->setOrderItemClass($this->cartService->getOrderItemClass());

            $messageBody = $this->container->get('twig')->render("pz/cart/email/invoice.twig", array(
                'orderContainer' => $orderContainer,
            ));

            $message = (new \Swift_Message())
                ->setSubject('Invoice #' . $orderContainer->getUniqid())
                ->setFrom(array(getenv('EMAIL_FROM')))
                ->setTo(array($data['email']))
                ->setBody($messageBody, 'text/html');
            $this->container->get('mailer')->send($message);

        }

        $totalSpent = $this->cartService->getOrderClass()::active($pdo, array(
            'select' => 'SUM(m.total) AS total',
            'whereSql' => 'm.customerId = ? AND m.payStatus = 2',
            'params' => array($customer->getId()),
            'orm' => 0,
            'oneOrNull' => 1,
        ));

        $totalAddresses = CustomerAddress::active($pdo, array(
            'select' => 'COUNT(m.id) AS count',
            'whereSql' => 'm.customerId = ?',
            'params' => array($customer->getId()),
            'orm' => 0,
            'oneOrNull' => 1,
        ));

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-dashboard.html.twig', array(
            'node' => $page,
            'orders' => $orders,
            'submitted' => $submitted,
            'form' => $form->createView(),
            'totalSpent' => $totalSpent['total'],
            'totalAddresses' => $totalAddresses['count'],
        ));
    }

    /**
     * @route("/account/orders")
     * @return Response
     */
    public function accountOrders()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $pagination = $request->get('pagination') ?: 1;
        $limit = 20;

        $orders = $this->cartService->getOrderClass()::active($pdo, array(
            'whereSql' => 'm.customerId = ? AND m.payStatus = ?',
            'params' => array($customer->getId(), CartService::STATUS_SUCCESS),
            'sort' => 'm.id',
            'order' => 'DESC',
            'page' => $pagination,
            'limit' => $limit,
        ));
        $total = $this->cartService->getOrderClass()::active($pdo, array(
            'whereSql' => 'm.customerId = ?',
            'params' => array($customer->getId()),
            'count' => 1,
        ));

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\ResendReceipt::class, null, array(
            'orderId' => $request->get('resend_receipt')['orderId'],
            'orderClass' => $this->cartService->getOrderClass(),
            'container' => $this->container,
        ));

        $submitted = 0;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = 1;

            $data = $form->getData();
            $orderContainer = $this->cartService->getOrderClass()::getByField($pdo, 'uniqid', $data['orderId']);
            $orderContainer->setOrderItemClass($this->cartService->getOrderItemClass());

            $messageBody = $this->container->get('twig')->render("pz/cart/email/invoice.twig", array(
                'orderContainer' => $orderContainer,
            ));

            $message = (new \Swift_Message())
                ->setSubject('Invoice #' . $orderContainer->getUniqid())
                ->setFrom(array(getenv('EMAIL_FROM')))
                ->setTo(array($data['email']))
                ->setBody($messageBody, 'text/html');
            $this->container->get('mailer')->send($message);

        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-orders.html.twig', array(
            'node' => $page,
            'orders' => $orders,
            'pagination' => $pagination,
            'total' => ceil($total['count'] / $limit),
            'url' => $request->getPathInfo(),
            'submitted' => $submitted,
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/account/order-detail/{slug}/{id}")
     * @return Response
     */
    public function accountOrder($slug = null, $id = null)
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $orderContainer = $this->cartService->getOrderClass()::getById($pdo, $id);
        if (!$orderContainer) {
            throw new NotFoundHttpException();
        }
        if ($orderContainer->getCustomerId() != $customer->getId()) {
            throw new NotFoundHttpException();
        }
        if ($orderContainer->getPayStatus() != CartService::STATUS_SUCCESS) {
            throw new NotFoundHttpException();
        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-order.html.twig', array(
            'node' => $page,
            'orderContainer' => $orderContainer,
        ));
    }

    /**
     * @route("/account/favourites")
     * @return Response
     */
    public function accountFavourites()
    {
        if (getenv('account_FAV_ENABLED') != 1) {
            throw new NotFoundHttpException();
        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-favourites.html.twig', array(
            'node' => $page,
        ));
    }

    /**
     * @route("/account/addresses")
     * @return Response
     */
    public function accountAddresses()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $customerAddresses = CustomerAddress::active($pdo, array(
            'whereSql' => 'm.customerId = ?',
            'params' => array($customer->getId()),
        ));

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-addresses.html.twig', array(
            'node' => $page,
            'customerAddresses' => $customerAddresses,
        ));
    }

    /**
     * @route("/account/address-detail")
     * @route("/account/address-detail/{slug}/{id}")
     * @return Response
     */
    public function accountAddress($slug = null, $id = null)
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $customerAddress = new CustomerAddress($pdo);
        $customerAddress->setCustomerId($customer->getId());
        if ($id) {
            $customerAddress = CustomerAddress::getById($pdo, $id);
            if (!$customerAddress) {
                throw new NotFoundHttpException();
            }
            if ($customerAddress->getCustomerId() != $customer->getId()) {
                throw new NotFoundHttpException();
            }
        }

        //convert 1/0 to boolean
        $customerAddress->setPrimaryAddress($customerAddress->getPrimaryAddress() ? true : false);

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\AccountAddress::class, $customerAddress, array(
            'container' => $this->container,
        ));

        $submitted = 0;
        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = 1;
            if ($request->get('submit') == 'Save') {
                $customerAddresses = CustomerAddress::active($pdo, array(
                    'whereSql' => 'm.customerId = ? AND m.id != ?',
                    'params' => array($customer->getId(), $id),
                ));

                if ($customerAddress->getPrimaryAddress() == 1) {

                    foreach ($customerAddresses as $itm) {
                        if ($itm->getPrimaryAddress() == 1) {
                            $itm->setPrimaryAddress(0);
                            $itm->save();
                        }
                    }

                } else {
                    if (count($customerAddresses)) {
                        $hasPrimaryAddress = 0;
                        foreach ($customerAddresses as $itm) {
                            if ($itm->getPrimaryAddress() == 1) {
                                $hasPrimaryAddress = 1;
                            }
                        }
                        if (!$hasPrimaryAddress) {
                            $customerAddresses[0]->setPrimaryAddress(1);
                            $customerAddresses[0]->save();
                        }
                    } else {
                        //must have at least one primary address
                        $customerAddress->setPrimaryAddress(true);
                        $form = $formFactory->create(\Pz\Form\Builder\AccountAddress::class, $customerAddress, array(
                            'container' => $this->container,
                        ));
                    }
                }
                $customerAddress->save();

            } elseif ($request->get('submit') == 'Delete') {

                $customerAddress->delete();

            }

            return new RedirectResponse('/account/addresses');
        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-address.html.twig', array(
            'node' => $page,
            'submitted' => $submitted,
            'form' => $form->createView(),
            'customerAddress' => $customerAddress,
        ));
    }

    /**
     * @route("/account/password")
     * @return Response
     */
    public function accountPassword()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\AccountPassword::class, $customer, array(
            'container' => $this->container,
        ));

        $submitted = 0;
        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = 1;
            $customer->save();
        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-password.html.twig', array(
            'node' => $page,
            'submitted' => $submitted,
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/account/profile")
     * @return Response
     */
    public function accountProfile()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();
        
        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        /** @var Form $form */
        $form = $formFactory->create(\Pz\Form\Builder\AccountProfile::class, $customer, array(
            'container' => $this->container,
        ));

        $submitted = 0;
        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = 1;
            $customer->save();
        }

        $pageClass = $this->pageService->getPageClass();
        $page = new $pageClass($pdo);
        $page->setTitle('My Account');
        return $this->render('pz/cart/account-profile.html.twig', array(
            'node' => $page,
            'submitted' => $submitted,
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/xhr/account/address/delete")
     * @return Response
     */
    public function xhrAccountAddressDelete()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $id = $request->get('id');

        $customerAddress = CustomerAddress::getById($pdo, $id);
        if (!$customerAddress) {
            throw new NotFoundHttpException();
        }
        if ($customerAddress->getCustomerId() != $customer->getId()) {
            throw new NotFoundHttpException();
        }

        $customerAddress->delete();
        return new JsonResponse($customerAddress);
    }

    /**
     * @route("/xhr/account/address/primary")
     * @return Response
     */
    public function xhrAccountAddressPrimary()
    {
        $customer = $this->container->get('security.token_storage')->getToken()->getUser();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $id = $request->get('id');

        $customerAddress = CustomerAddress::getById($pdo, $id);
        if (!$customerAddress) {
            throw new NotFoundHttpException();
        }
        if ($customerAddress->getCustomerId() != $customer->getId()) {
            throw new NotFoundHttpException();
        }

        $customerAddress->setPrimaryAddress(1);
        $customerAddress->save();

        $customerAddresses = CustomerAddress::active($pdo, array(
            'whereSql' => 'm.customerId = ? AND m.id != ?',
            'params' => array($customer->getId(), $id),
        ));

        foreach ($customerAddresses as $itm) {
            if ($itm->getPrimaryAddress() == 1) {
                $itm->setPrimaryAddress(0);
                $itm->save();
            }
        }

        return new JsonResponse($customerAddress);
    }
}