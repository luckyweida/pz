<?php

namespace Pz\Controller;

use Doctrine\DBAL\Connection;
use Pz\Orm\_Model;
use Pz\Router\Mo;
use Pz\Router\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class John extends Mo
{
    /**
     * @route("/pz/models/customised/detail/{modelId}", name="model")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function model($modelId)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

//        var_dump(_Model::sync($pdo));exit;

        $loader = $this->container->get('twig')->getLoader();
        $loader->setPaths(array(
            $this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/templates',
            $this->container->getParameter('kernel.project_dir') . '/vendor/symfony/twig-bridge/Resources/views/Form',
        ));

        $params = $this->getParams();

//        if ($modelId) {
//            $model = _Model::getById($pdo, $modelId);
//            if (!$model) {
//            }
//        } else {
//            $model = new _Model($pdo);
//            $model->label = 'New models';
//            $model->className = 'NewModel';
//            $model->namespace = 'Web\\ORMs';
//            $model->modelType = 1;
//            $model->dataType = 0;
//            $model->listType = 0;
//            $model->numberPerPage = 25;
//            $model->defaultSortBy = 'id';
//            $model->defaultOrder = 1;
//        }

        $model = new _Model($pdo);
        $model->setTitle('New models');
        $model->setClassName('NewModel');
        $model->setNamespace('Web\\ORMs');
        $model->setModelType(0);
        $model->setDataType(0);
        $model->setListType(0);
        $model->setNumberPerPage(25);
        $model->setDefaultSortBy('id');
        $model->setDefaultOrder(1);

        $form = $this->createForm(\Pz\Form\Model::class, $model);
        $params['form'] = $form->createView();
        $params['model'] = $model;

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
//            $options['model']->save();
//            if ($options['model']->modelType == 0) {
//                ModelIO::generateOrmFile($options['model']);
//                ModelIO::generateCustomOrmFile($options['model']);
//            }
//
//            $options['returnUrl'] = $request->get('returnUrl') ?: '/pz/models/' . $options['model']->modelType;
//            if ($request->get('submit') == 'apply') {
//                return $app->redirect($app->url('edit-model', array(
//                        'modelType' => $options['model']->modelType,
//                        'id' => $options['model']->id,
//                    )) . '?returnUrl=' . urlencode($options['returnUrl']));
//
//            } else if ($request->get('submit') == 'save') {
//                return $app->redirect($options['returnUrl']);
//            }
        }

        $CMS_WIDGETS = array(
            '\\Eva\\Forms\\Types\\FormData' => '*Form data',
            '\\Eva\\Forms\\Types\\AssetPicker' => 'Asset picker',
            '\\Eva\\Forms\\Types\\AssetFolderPicker' => 'Asset folder picker',
            '\\Eva\\Forms\\Types\\ChoiceMultiJson' => 'Choice multi json',
            '\\Eva\\Forms\\Types\\DatePicker' => 'Date picker',
            '\\Eva\\Forms\\Types\\DateTimePicker' => 'Date time picker',
            '\\Eva\\Forms\\Types\\Wysiwyg' => 'Wysiwyg',
            '\\Eva\\Forms\\Types\\ContentBlock' => 'Content blocks',
            'checkbox' => 'Checkbox',
            'choice' => 'Choice',
            'email' => 'Email',
            'password' => 'Password',
            'text' => 'Text',
            'textarea' => 'Textarea',
            'hidden' => 'Hidden',
        );

        $params['fields'] = array('text', 'date');
        $params['metas'] = array();
        $params['widgets'] = $CMS_WIDGETS;
        return $this->render($params['node']->getTemplate(), $params);
    }



    /**
     * @route("/pz/{page}", requirements={"page" = ".*"}, name="john")
     * @return Response
     */
    public function john()
    {
//        $loader = $this->container->get('twig')->getLoader();
//        $loader->addPath(__DIR__ . '/../../../templates/');

        $params = $this->getParams();
        return $this->render($params['node']->getTemplate(), $params);
    }

    public function getNodes()
    {
        $nodes = array();
        $nodes[] = new Node(1, 'Pages', 0, 0, '/pz/pages', 'pages.twig');
        $nodes[] = new Node(2, 'Database', 0, 1);
        $nodes[] = new Node(3, 'Files', 0, 2, '/pz/files', 'files.twig');
        $nodes[] = new Node(4, 'Admin', 0, 3);
        $nodes[] = new Node(11, 'Page', 1, 0, '/pz/pages/detail', 'page.twig', 2, 1, 1);
        $nodes[] = new Node(41, 'Customised Models', 4, 0, '/pz/models/customised', 'models.twig');
        $nodes[] = new Node(411, 'Customised Model', 41, 0, '/pz/models/customised/detail', 'model.twig', 2, 1, 1);
        $nodes[] = new Node(42, 'Built-in Models', 4, 1, '/pz/models/built-in', 'models.twig', 2, 1, 1);
        $nodes[] = new Node(421, 'Built-in Model', 42, 0, '/pz/models/built-in/detail', 'model.twig', 2, 1, 1);

        $nodes[0]->addExtra('icon', 'fa fa-sitemap');
        $nodes[1]->addExtra('icon', 'fa fa-database');
        $nodes[2]->addExtra('icon', 'fa fa-file-image-o');
        $nodes[3]->addExtra('icon', 'fa fa-cogs');

        return $nodes;
    }
}