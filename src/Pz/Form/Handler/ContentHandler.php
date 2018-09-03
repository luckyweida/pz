<?php

namespace Pz\Form\Handler;

use Pz\Orm\_Model;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ContentHandler
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(_Model $model, $orm)
    {
        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');
        $formBuilder = $formFactory->createBuilder(FormType::class, $orm);

        $columnsJson = json_decode($model->getColumnsJson());
//        var_dump($columnsJson);exit;
        foreach ($columnsJson as $itm) {
            $widget = $itm->widget;
            if (strpos($itm->widget, '\\') !== FALSE) {
                $widget = $itm->widget;
            }
            $opts = array(
                'label' => $itm->label,
            );
            if ($itm->widget == 'choice' || $itm->widget == '\\Pz\\Form\\Type\\ChoiceMultiJson') {
//                $conn = $this->app['zdb']->getConnection();
//                $stmt = $conn->prepare($itm->sql);
//                $stmt->execute();
//                $choices = array();
//                foreach ($stmt->fetchAll() as $key => $val) {
//                    $choices[$val['key']] = $val['value'];
//                }
//                $opts['choices'] = $choices;
////                $opts['empty_data'] = null;
//                $opts['required'] = false;
////                $opts['placeholder'] = 'Select an option...';
            }
            if ($itm->required == 1) {
                $opts['constraints'] = array(
                    new Assert\NotBlank(),
                );
            }
            $formBuilder->add($itm->field, $widget, $opts);
        }

        $form = $formBuilder->getForm();

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $orm->save();
        }

        return $form->createView();
    }
}