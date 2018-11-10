<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\Asset;
use Pz\Orm\AssetOrm;
use Pz\Orm\Page;
use Pz\Orm\PageCategory;
use Pz\Router\Node;
use Pz\Router\Tree;
use Pz\Service\Db;
use Pz\Twig\Extension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


class AjaxAsset extends Controller
{
    /**
     * @route("/pz/ajax/asset/files/chosen/rank", name="pzAjaxAssetFilesChosenRank")
     * @return Response
     */
    public function pzAjaxAssetFilesChosenRank()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $modelName = $request->get('modelName');
        $attributeName = $request->get('attributeName');
        $ormId = $request->get('ormId');
        $ids = json_decode($request->get('ids'));
        foreach ($ids as $idx => $id) {

            /** @var AssetOrm $orm */
            $orm = AssetOrm::data($pdo, array(
                'whereSql' => 'm.title = ? AND m.modelName = ? AND m.attributeName = ? AND ormId = ?',
                'params' => array($id, $modelName, $attributeName, $ormId),
                'oneOrNull' => 1,
            ));
            if ($orm) {
                $orm->setMyRank($idx);
                $orm->save();
            }
        }

        return new JsonResponse($ids);
    }

    /**
     * @route("/pz/ajax/asset/files/chosen", name="pzAjaxAssetFilesChosen")
     * @return Response
     */
    public function pzAjaxAssetFilesChosen()
    {
        $data = array();

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $modelName = $request->get('modelName');
        $attributeName = $request->get('attributeName');
        $ormId = $request->get('ormId');
        if ($modelName && $attributeName && $ormId) {

            /** @var AssetOrm[] $result */
            $result = AssetOrm::data($pdo, array(
                'whereSql' => 'm.modelName = ? AND m.attributeName = ? AND ormId = ?',
                'params' => array($modelName, $attributeName, $ormId),
                'sort' => 'm.myRank',
            ));

            foreach ($result as $itm) {
                $data[] = $itm->objAsset();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @route("/pz/ajax/asset/files", name="pzAjaxAssetFiles")
     * @return Response
     */
    public function pzAjaxAssetFiles()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();

        $keyword = $request->get('keyword') ?: '';
        $currentFolderId = $request->get('currentFolderId') ?: 0;
        $this->container->get('session')->set('currentFolderId', $currentFolderId);

        if ($keyword) {
            $data = Asset::data($pdo, array(
                'whereSql' => 'm.isFolder = 0 AND m.title LIKE ?',
                'params' => array("%$keyword%"),
            ));
        } else {
            $data = Asset::data($pdo, array(
                'whereSql' => 'm.isFolder = 0 AND m.parentId = ?',
                'params' => array($currentFolderId),
            ));
        }

        $modelName = $request->get('modelName');
        $attributeName = $request->get('attributeName');
        $ormId = $request->get('ormId');
        if ($modelName && $attributeName && $ormId) {
            $assetOrmMap = array();
            /** @var AssetOrm[] $result */
            $result = AssetOrm::data($pdo, array(
                'whereSql' => 'm.modelName = ? AND m.attributeName = ? AND ormId = ?',
                'params' => array($modelName, $attributeName, $ormId),
            ));
            foreach ($result as $itm) {
                $assetOrmMap[$itm->getTitle()] = 1;
            }

            foreach ($data as &$itm) {
                $itm = json_decode(json_encode($itm));
                $itm->_selected = isset($assetOrmMap[$itm->id]) ? 1 : 0;
            }
        }


        return new JsonResponse(array(
            'files' => $data,
        ));
    }

    /**
     * @route("/pz/ajax/asset/folders", name="pzAjaxAssetFolders")
     * @return Response
     */
    public function pzAjaxAssetFolders()
    {
        $request = Request::createFromGlobals();
        $currentFolderId = $request->get('currentFolderId') ?: 0;
        $this->container->get('session')->set('currentFolderId', $currentFolderId);

        $root = $this->getFolderRoot($currentFolderId);

        return new JsonResponse(array(
            'folders' => $root,
        ));
    }

    /**
     * @route("/pz/ajax/asset/folders/file/select", name="pzAjaxAssetFoldersFileSelect")
     * @return Response
     */
    public function pzAjaxAssetFoldersFileSelect()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $addOrDelete = $request->get('addOrDelete') ?: 0;
        $ids = $request->get('id');

        $modelName = $request->get('modelName');
        $ormId = $request->get('ormId');
        $attributeName = $request->get('attributeName');

        if ($addOrDelete == 0) {
            foreach ($ids as $id) {
                $assetOrms = AssetOrm::data($pdo, array(
                    'whereSql' => 'm.title = ? AND m.modelName = ? AND m.attributeName = ? AND ormId = ?',
                    'params' => array($id, $modelName, $attributeName, $ormId),
                ));
                foreach ($assetOrms as $assetOrm) {
                    $assetOrm->delete();
                }
            }
        } elseif ($addOrDelete == 1) {

            foreach ($ids as $id) {
                $assetOrm = AssetOrm::data($pdo, array(
                    'whereSql' => 'm.title = ? AND m.modelName = ? AND m.attributeName = ? AND ormId = ?',
                    'params' => array($id, $modelName, $attributeName, $ormId),
                    'oneOrNull' => 1,
                ));
                if (!$assetOrm) {
                    $assetOrm = new AssetOrm($pdo);
                    $assetOrm->setTitle($id);
                    $assetOrm->setModelName($modelName);
                    $assetOrm->setAttributeName($attributeName);
                    $assetOrm->setOrmId($ormId);
                    $assetOrm->setMyRank(999);
                    $assetOrm->save();
                }
            }
        }

        elseif ($addOrDelete == 2) {

            $assetOrms = AssetOrm::data($pdo, array(
                'whereSql' => 'm.modelName = ? AND m.attributeName = ? AND ormId = ?',
                'params' => array($modelName, $attributeName, $ormId),
//                'debug' => 1,
            ));

            foreach ($assetOrms as $assetOrm) {
                $assetOrm->delete();
            }
        }

        return new JsonResponse($ids);
    }

    /**
     * @route("/pz/ajax/asset/nav", name="pzAjaxAssetNav")
     * @return Response
     */
    public function pzAjaxAssetNav()
    {
        $request = Request::createFromGlobals();
        $currentFolderId = $request->get('currentFolderId') ?: 0;
        $this->container->get('session')->set('currentFolderId', $currentFolderId);

        $root = $this->getFolderRoot($currentFolderId);
        $path = $root->path($currentFolderId);

        return new JsonResponse(array(
            'currentFolder' => end($path),
            'path' => $path,
        ));
    }

    /**
     * @route("/pz/ajax/asset/files/add/folder", name="pzAjaxAssetAddFolder")
     * @return Response
     */
    public function pzAjaxAssetAddFolder()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $title = $request->get('title');
        $parentId = $request->get('parentId');

        $rank = Asset::data($pdo, array(
            'select' => 'MAX(m.rank) AS max',
            'orm' => 0,
            'whereSql' => 'm.parentId = ?',
            'params' => array($request->get('__parentId')),
            'oneOrNull' => 1,
        ));
        $max = ($rank['max'] ?: 0) + 1;

        $orm = new Asset($pdo);
        $orm->setTitle($title);
        $orm->setParentId($parentId);
        $orm->setRank($max);
        $orm->setIsFolder(1);
        $orm->save();
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/asset/files/edit/folder", name="pzAjaxAssetEditFolder")
     * @return Response
     */
    public function pzAjaxAssetEditFolder()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();

        /** @var Asset $orm */
        $orm = Asset::getById($pdo, $request->get('id'));
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $orm->setTitle($request->get('title'));
        $orm->save();
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/asset/folders/update", name="pzAjaxAssetFoldersUpdate")
     * @return Response
     */
    public function pzAjaxAssetFoldersUpdate()
    {

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $data = json_decode($request->get('data'));
        foreach ($data as $itm) {
            /** @var Asset $orm */
            $orm = Asset::getById($pdo, $itm->id);
            $orm->setParentId($itm->parentId);
            $orm->setRank($itm->rank);
            $orm->save();
        }
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/asset/file/move", name="pzAjaxAssetFileMove")
     * @return Response
     */
    public function pzAjaxAssetFileMove()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $data = json_decode($request->get('data'));

        /** @var Asset $orm */
        $orm = Asset::getById($pdo, $request->get('id'));
        $orm->setParentId($request->get('parentId'));
        $orm->save();
        return new Response('OK');

    }

    /**
     * @route("/pz/ajax/asset/files/delete/folder", name="pzAjaxAssetDeleteFolder")
     * @return Response
     */
    public function pzAjaxAssetDeleteFolder()
    {
        $request = Request::createFromGlobals();
        $id = $request->get('id');

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $orm = Asset::getById($pdo, $id);
        if ($orm) {
            $this->deleteFolder($pdo, $orm);
        }

        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/asset/files/delete/file", name="pzAjaxAssetDeleteFile")
     * @return Response
     */
    public function pzAjaxAssetDeleteFile()
    {
        $request = Request::createFromGlobals();
        $id = $request->get('id');

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        /** @var Asset $orm */
        $orm = Asset::getById($pdo, $id);
        if (!$orm) {
            throw new NotFoundHttpException();
        }
        if (file_exists($this->container->getParameter('kernel.project_dir') . '/uploads/' . $orm->getFileLocation())) {
            unlink($this->container->getParameter('kernel.project_dir') . '/uploads/' . $orm->getFileLocation());
        }
        $orm->delete();
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/asset/files/upload", name="pzAjaxAssetUpload")
     * @return Response
     */
    public function pzAjaxAssetUpload()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();

        $files = $request->files->get('files');
        if ($files && is_array($files) && count($files) > 0) {
            $originalName = $files[0]->getClientOriginalName();
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);

            $rank = Asset::data($pdo, array(
                'select' => 'MIN(m.rank) AS min',
                'orm' => 0,
                'whereSql' => 'm.parentId = ?',
                'params' => array($request->get('parentId')),
                'oneOrNull' => 1,
            ));
            $min = $rank['min'] - 1;

            $orm = new Asset($pdo);
            $orm->setIsFolder(0);
            $orm->setParentId($request->get('parentId'));
            $orm->setRank($min);
            $orm->setTitle($originalName);
            $orm->setFileName($originalName);
            $orm->save();

            require_once $this->container->getParameter('kernel.project_dir') . '/vendor/blueimp/jquery-file-upload/server/php/UploadHandler.php';
            $uploader = new \UploadHandler(array(
                'upload_dir' => $this->container->getParameter('kernel.project_dir') . '/uploads/',
                'image_versions' => array()
            ), false);
            $_SERVER['HTTP_CONTENT_DISPOSITION'] = $orm->getId();
            $result = $uploader->post(false);

            $orm->setFileLocation($orm->getId() . '.' . $ext);
            $orm->setFileType($result['files'][0]->type);
            $orm->setFileSize($result['files'][0]->size);
            $orm->save();

            if (file_exists($this->container->getParameter('kernel.project_dir') . '/uploads/' . $result['files'][0]->name)) {
                rename($this->container->getParameter('kernel.project_dir') . '/uploads/' . $result['files'][0]->name, dirname($_SERVER['SCRIPT_FILENAME']) . '/../uploads/' . $orm->getId() . '.' . $ext);
            }

            return new JsonResponse($orm);
        }
        return new Response(json_encode(array(
            'failed'
        )));
    }

    /**
     * @param \PDO $pdo
     * @param Asset $orm
     */
    private function deleteFolder(\PDO $pdo, Asset $orm)
    {
        /** @var Asset[] $children */
        $children = Asset::data($pdo, array(
            'whereSql' => 'm.parentId = ?',
            'params' => array($orm->getId())
        ));
        foreach ($children as $itm) {
            $this->deleteFolder($pdo, $itm);
        }
        if (!$orm->getIsFolder()) {
            if (file_exists($this->container->getParameter('kernel.project_dir') . '/uploads/' . $orm->getFileLocation())) {
                unlink($this->container->getParameter('kernel.project_dir') . '/uploads/' . $orm->getFileLocation());
            }
        }
        $orm->delete();
    }

    /**
     *
     */
    private function getFolderRoot($currentFolderId)
    {
        $folderOpenMaxLimit = 10;

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $baseurl = '/pz/files?currentFolderId=';
        $childrenCount = array();
        $nodes = array();

        /** @var Asset[] $data */
        $data = Asset::data($pdo, array('whereSql' => 'm.isFolder = 1'));
        foreach ($data as $itm) {
            if (!isset($childrenCount[$itm->getParentId()])) {
                $childrenCount[$itm->getParentId()] = 0;
            }
            $childrenCount[$itm->getParentId()]++;

            $node = new Node($itm->getId(), $itm->getTitle() ?: 'Home', $itm->getParentId() ?: 0, $itm->getRank(), $baseurl . $itm->getId());
            $node->setText($itm->getTitle());
            $node->setState(array('opened' => true, 'selected' => $currentFolderId == $itm->getId()));
            $nodes[] = $node;
        }

        /** @var Node[] $nodes */
        foreach ($nodes as &$itm) {
            if (isset($childrenCount[$itm->getId()]) && $childrenCount[$itm->getId()] >= $folderOpenMaxLimit && $itm->getId() != $currentFolderId) {
                $itm->setStateValue('opened', false);
            }
        }
        $tree = new Tree($nodes);

        $root = $tree->getRoot();
        $root->setTitle('Home');
        $root->setText('Home');
        $root->setUrl($baseurl . 0);
        $root->setState(array('opened' => true, 'selected' => false));
        if ($currentFolderId === 0) {
            $root->setStateValue('selected', 1);
        }
        return $root;
    }
}