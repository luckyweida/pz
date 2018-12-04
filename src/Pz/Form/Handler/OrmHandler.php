<?php

namespace Pz\Form\Handler;

use Cocur\Slugify\Slugify;
use Pz\Orm\_Model;
use Pz\Redirect\RedirectException;
use Pz\Router\Node;
use Pz\Router\Tree;
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
                    if (substr($matches[1], 0, 1) == '_') {
                        $tablename = $matches[1];
                    } else {
                        $tablename = $slugify->slugify($matches[1]);
                    }

                    $itm->sql = str_replace($matches[0], "FROM $tablename", $itm->sql);
                }

                $stmt = $pdo->prepare($itm->sql);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_OBJ);

                $opts['choices'] = array(
                    '@1' => ''
                );
                foreach ($result as $key => $val) {
                    $opts['choices'][$val->value] = $val->key;
                }
                $opts['required'] = false;
            } elseif (
                $itm->widget == '\\Pz\\Form\\Type\\ChoiceMultiJsonTree'
                || $itm->widget == '\\Pz\\Form\\Type\\ChoiceTree'
            ) {

                $slugify = new Slugify(['trim' => false]);
                preg_match('/\bfrom\b\s*(\w+)/i', $itm->sql, $matches);
                if (count($matches) == 2) {
                    if (substr($matches[1], 0, 1) == '_') {
                        $tablename = $matches[1];
                    } else {
                        $tablename = $slugify->slugify($matches[1]);
                    }

                    $itm->sql = str_replace($matches[0], "FROM $tablename", $itm->sql);
                }

                $stmt = $pdo->prepare($itm->sql);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_OBJ);

                $nodes = array();
                foreach ($result as $key => $val) {
                    $node = new Node($val->key, $val->value, $val->parentId ?: 0, $key);
                    $nodes[] = $node;
                }
                $tree = new Tree($nodes);
                $root = $tree->getRoot();

                $result = static::tree2Array($root, 1);
                $opts['choices'] = array();
                foreach ($result as $key => $val) {
                    $opts['choices'][$val->value] = $val->key;
                }
                $opts['required'] = false;
            } else if ($itm->widget == '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType') {
                $getMethod = 'get' . ucfirst($itm->field);
                $setMethod = 'set' . ucfirst($itm->field);
                $orm->$setMethod($orm->$getMethod() ? true : false);
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
            $isNew = $orm->getId() ? 0 : 1;
            $orm->save();

            if ($request->get('submit') == 'Save') {
                $returnUrl = $request->get('returnUrl') ?: '/pz/' . ($model->getDataType() == 0 ? 'database' : 'admin') . '/' . $model->getId();
                throw new RedirectException($returnUrl, 301);
            }

            $returnUrl = '/pz/' . ($model->getDataType() == 0 ? 'database' : 'admin') . '/' . $model->getId() . '/detail/' . $orm->getId();
            throw new RedirectException($returnUrl, 301);
        }

        return $form->createView();
    }

    static public function tree2Array(Node $node, $depth) {
        $data = array();
        foreach ($node->getChildren() as $child) {
            $obj = new \stdClass();
            $obj->key = $child->getId();
            $obj->value = "{$child->getTitle()}@{$depth}";
            $data[] = $obj;

            $result = static::tree2Array($child, $depth + 1);
            $data = array_merge($data, $result);
        }
        return $data;
    }
}