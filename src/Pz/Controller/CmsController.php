<?php

namespace Pz\Controller;

use Pz\Orm\_Model;
use Pz\Axiom\Mo;
use Pz\Orm\AssetOrm;
use Pz\Orm\DataGroup;
use Pz\Router\NodePage;
use Pz\Service\DbService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CmsController extends Mo
{
    /**
     * @route("/pz/login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $request = Request::createFromGlobals();
        $requestUri = rtrim($request->getPathInfo(), '/');
        $params = $this->getParams($requestUri);

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('pz/login.twig', array_merge($params, array(
            'last_username' => $lastUsername,
            'error' => $error,
        )));
    }

    /**
     * @route("/pz/admin/models/{type}/copy/{modelId}", requirements={"type" = "customised|built-in"})
     * @return Response
     */
    public function copyModel($modelId)
    {
        $request = Request::createFromGlobals();
        $requestUri = rtrim($request->getPathInfo(), '/');
        $requestUri = str_replace('/copy/', '/detail/', $requestUri);
        $params = $this->getParams($requestUri);

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();
        /** @var _Model $model */
        $model = _Model::getById($pdo, $modelId);
        $model->setTitle('New ' . $model->getTitle());
        $model->setClassName('New' . $model->getClassName());
        $model->setId(null);
        $model->setUniqid(uniqid());
        $params['model'] = $model;

        return $this->render($params['node']->getTemplate(), $params);
    }

    /**
     * @route("/pz/{section}/{modelId}/copy/{ormId}", requirements={"section" = "database|admin"})
     * @return Response
     */
    public function copyOrm($modelId, $ormId)
    {
        $request = Request::createFromGlobals();
        $requestUri = rtrim($request->getPathInfo(), '/');
        $requestUri = str_replace('/copy/', '/detail/', $requestUri);
        $params = $this->getParams($requestUri);

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();
        /** @var _Model $model */
        $model = _Model::getById($pdo, $modelId);
        $fullClassName = DbService::fullClassName($model->getClassName());
        $orm = $fullClassName::getById($pdo, $ormId);
        $uniqId = $orm->getUniqid();

        $orm->setId(null);
        $orm->setUniqid(uniqid());

        /** @var AssetOrm[] $result */
        $result = AssetOrm::data($pdo, array(
            'whereSql' => 'm.modelName = ? AND m.ormId = ?',
            'params' => array($model->getClassName(), $uniqId),
        ));

        foreach ($result as $itm) {
            $itm->setId(null);
            $itm->setUniqid(uniqid());
            $itm->setOrmId($orm->getUniqid());
            $itm->save();
        }
//        $orm->setTitle('New ' . $orm->getTitle());
        $params['orm'] = $orm;

        return $this->render($params['node']->getTemplate(), $params);
    }

    /**
     * @route("/pz/{page}", requirements={"page" = ".*"})
     * @return Response
     */
    public function cms()
    {
        $request = Request::createFromGlobals();
        $requestUri = rtrim($request->getPathInfo(), '/');
        $params = $this->getParams($requestUri);
        return $this->render($params['node']->getTemplate(), $params);
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        /** @var \PDO $pdo */
        $pdo = $this->connection->getWrappedConnection();
        $nodes = array();
        $nodes[] = new NodePage(-10, -1, 0, 1, 'Login', '/pz/login', 'pz/login.twig');
        $nodes[] = new NodePage(1, null, 0, 1, 'Pages', '/pz/pages', 'pz/pages.twig', 'fa fa-sitemap');
        $nodes[] = new NodePage(11, 1, 0, 0, 'Page', '/pz/admin/6/detail', 'pz/orm.twig');

        /** @var DataGroup[] $dataGroups */
        $dataGroups = DataGroup::active($pdo);
        foreach ($dataGroups as $dgIdx => $dataGroup) {
            $dgId = 2 + $dgIdx;

            $dgNode = new NodePage($dgId, null, $dgId, 1, $dataGroup->getTitle(), '', '', $dataGroup->getIcon());

            /** @var _Model[] $result */
            $result = _Model::active($pdo, array(
                'whereSql' => 'm.dataGroups LIKE ? AND m.dataType = 0',
                'params' => array('%"' . $dataGroup->getId() . '"%'),
            ));

            if (count($result)) {
                $nodes[] = $dgNode;

                foreach ($result as $idx => $itm) {
                    $fullClass = DbService::fullClassName($itm->getClassName());
                    $nodes[] = new NodePage($dgId . '-' . $itm->getId(), $dgId, $idx, 1, $itm->getTitle(), "/pz/database/" . $itm->getId(), $fullClass::getCmsOrmsTwig());
                    $nodes[] = new NodePage($dgId . '-' . $itm->getId() . '-1', $dgId . '-' . $itm->getId(), 0, 0, $itm->getTitle(), "/pz/database/" . $itm->getId() . '/detail', $fullClass::getCmsOrmTwig(), '', 2, 1);
                }
            }
        }

        $nodes[] = new NodePage(30, null, 30, 1, 'Files', '/pz/files', 'pz/files.twig', 'fa fa-file-image-o');
        $nodes[] = new NodePage(40, null, 40, 1, 'Admin', '', '', 'fa fa-cogs');
        $nodes[] = new NodePage(41, 40, 998, 1, 'Customised Models', '/pz/admin/models/customised', 'pz/models.twig');
        $nodes[] = new NodePage(411, 41, 0, 0, 'Customised Model', '/pz/admin/models/customised/detail', 'pz/model.twig', '', 2, 1);;
        $nodes[] = new NodePage(42, 40, 999, 1, 'Built-in Models', '/pz/admin/models/built-in', 'pz/models.twig');
        $nodes[] = new NodePage(421, 42, 0, 0, 'Built-in Model', '/pz/admin/models/built-in/detail', 'pz/model.twig', '', 2, 1);
        $nodes[] = new NodePage(412, 41, 0, 0, 'Sync Model', '/pz/admin/models/customised/sync', 'pz/model-sync.twig', '', 2, 1);
        $nodes[] = new NodePage(422, 42, 0, 0, 'Sync Model', '/pz/admin/models/built-in/sync', 'pz/model-sync.twig', '', 2, 1);

        /** @var _Model[] $modelDatabase */
        $modelDatabase = _Model::active($pdo);
        foreach ($modelDatabase as $idx => $itm) {
            $fullClass = DbService::fullClassName($itm->getClassName());
            if ($itm->getDataType() == 1) {
                $nodes[] = new NodePage('40-' . $itm->getId(), 40, $idx, 1, $itm->getTitle(), "/pz/admin/" . $itm->getId(), $fullClass::getCmsOrmsTwig());
                $nodes[] = new NodePage('40-' . $itm->getId() . '-1', '40-' . $itm->getId(), 0, 0, $itm->getTitle(), "/pz/admin/" . $itm->getId() . '/detail', $fullClass::getCmsOrmTwig(), '', 2, 1);
            }
        }

        return $nodes;
    }
}