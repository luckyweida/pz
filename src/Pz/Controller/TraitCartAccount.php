<?php

namespace Pz\Controller;

use Pz\Orm\Customer;

use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;

use Pz\Orm\CustomerAddress;
use Pz\Service\CartService;

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

trait TraitCartAccount
{
    /**
     * @route("/login")
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('cart/login.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Login',
            ),
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
            $messageBody = $this->container->get('twig')->render("email/email-activate.twig", array(
                'customer' => $orm,
            ));


            $message = (new \Swift_Message())
                ->setSubject('West Brook - Acticate your account')
                ->setFrom('noreply@westbrook.co.nz')
                ->setTo($orm->getTitle())
                ->setBody($messageBody, 'text/html');


            $this->container->get('mailer')->send($message);

            $orm->setSource(CartService::CUSTOMER_WEBSITE);
            $orm->save();

            return new RedirectResponse('/activation/required?id=' . $orm->getUniqid());

        }

        return $this->render('cart/register.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Register',
            ),
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/activation/required")
     * @return Response
     */
    public function activationRequired()
    {
        return $this->render('cart/confirmation.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Activation Required',
            ),
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

            $messageBody = $this->container->get('twig')->render("email/email-forget.twig", array(
                'customer' => $orm,
            ));
            $message = (new \Swift_Message())
                ->setSubject('West Brook - Reset your password')
                ->setFrom('noreply@westbrook.co.nz')
                ->setTo($orm->getTitle())
                ->setBody($messageBody, 'text/html');


            $this->container->get('mailer')->send($message);

            return new RedirectResponse('/reset-password-email-sent?id=' . $orm->getUniqid());

        }

        return $this->render('cart/forget-password.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Forget Password',
            ),
            'form' => $form->createView(),
        ));
    }

    /**
     * @route("/reset-password-email-sent")
     * @return Response
     */
    public function resetPasswordEmailSent()
    {
        return $this->render('cart/confirmation.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'Reset Password Email Sent',
            ),
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
        return $this->render('cart/account-dashboard.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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

        $orders = $this->cartService->getOrderClass()::active($pdo, array(
            'whereSql' => 'm.customerId = ?',
            'params' => array($customer->getId()),
            'page' => $pagination,
            'limit' => 10,
        ));

        return $this->render('cart/account-orders.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
            'orders' => $orders,
        ));
    }

    /**
     * @route("/account/order-detail")
     * @return Response
     */
    public function accountOrder()
    {
        return $this->render('cart/account-order.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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

        return $this->render('cart/account-favourites.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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

        return $this->render('cart/account-addresses.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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

        return $this->render('cart/account-address.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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

        return $this->render('cart/account-password.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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

        return $this->render('cart/account-profile.html.twig', array(
            'node' => array(
                'description' => '',
                'pageTitle' => '',
                'title' => 'My Account',
            ),
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