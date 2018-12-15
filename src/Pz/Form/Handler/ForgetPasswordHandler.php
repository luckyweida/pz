<?php

namespace Pz\Form\Handler;

use Pz\Orm\Customer;
use Pz\Redirect\RedirectException;

use Cocur\Slugify\Slugify;
use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ForgetPasswordHandler
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle()
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

            throw new RedirectException('/reset-password-email-sent?id=' . $orm->getUniqid());
        }

        return $form->createView();
    }

}