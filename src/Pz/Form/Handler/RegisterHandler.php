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

class RegisterHandler
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

            $orm->setSource(Customer::WEBSITE);
            $orm->save();

            throw new RedirectException('/activation/required?id=' . $orm->getUniqid());

        }

        return $form->createView();
    }

}