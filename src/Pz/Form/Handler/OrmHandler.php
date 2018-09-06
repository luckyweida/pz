<?php

namespace Pz\Form\Handler;

use Cocur\Slugify\Slugify;
use Pz\Orm\_Model;
use Pz\Redirect\RedirectException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class OrmHandler
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(_Model $model, $orm)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

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
            if (
                $itm->widget == '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType'
                || $itm->widget == '\\Pz\\Form\\Type\\ChoiceMultiJson'
            ) {

                $slugify = new Slugify(['trim' => false]);
                preg_match('/\bfrom\b\s*(\w+)/i', $itm->sql, $matches);
                if (count($matches) == 2) {
                    $tablename = $slugify->slugify($matches[1]);
                    $itm->sql = str_replace($matches[0], "FROM $tablename", $itm->sql);
                }

                $stmt = $pdo->prepare($itm->sql);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_OBJ);

                $opts['choices'] = array();
                foreach ($result as $key => $val) {
                    $opts['choices'][$val->value] = $val->key;
                }
                $opts['required'] = false;
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

            if ($request->get('submit') == 'Save') {
                $returnUrl = $request->get('returnUrl') ?: '/pz/admin/' . $model->getId();
                throw new RedirectException($returnUrl, 301);
            }
        }

        return $form->createView();
    }
}