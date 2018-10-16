<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\Asset;
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


class Ajax extends Controller
{

    /**
     * @route("/pz/ajax/column/sort", name="pzAjaxColumnSort")
     * @return Response
     */
    public function pzAjaxColumnSort()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $data = json_decode($request->get('data'));
        $className = $request->get('className');

        $fullClassName = Db::fullClassName($className);
        foreach ($data as $idx => $itm) {
            $orm = $fullClassName::getById($pdo, $itm);
            if ($orm) {
                $orm->setRank($idx);
                $orm->save();
            }
        }
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/status", name="pzAjaxStatus")
     * @return Response
     */
    public function pzAjaxStatus()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $status = $request->get('status');
        $id = $request->get('id');
        $className = $request->get('className');

        $fullClassName = Db::fullClassName($className);
        $orm = $fullClassName::getById($pdo, $id);
        if ($orm) {
            $orm->setStatus($status);
            $orm->save();
        }
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/delete", name="pzAjaxDelete")
     * @return Response
     */
    public function pzAjaxDelete()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $status = $request->get('status');
        $id = $request->get('id');
        $className = $request->get('className');

        $fullClassName = Db::fullClassName($className);
        $orm = $fullClassName::getById($pdo, $id);
        if ($orm) {
            $orm->delete();
        }
        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/cat/count", name="pzAjaxCatCount")
     * @return Response
     */
    public function pzAjaxCatCount()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $pageCategories = PageCategory::active($pdo);
        $pages = Page::data($pdo);

        $result = array();
        foreach ($pageCategories as $pageCategory) {
            $result["cat{$pageCategory->getId()}"] = 0;
            foreach ($pages as $page) {
                $category = json_decode($page->getCategory());
                if (in_array($pageCategory->getId(), $category)) {
                    $result["cat{$pageCategory->getId()}"]++;
                }
            }
        }

        $result["cat0"] = 0;
        foreach ($pages as $page) {
            $category = json_decode($page->getCategory());
            if (count($category) == 0) {
                $result["cat0"]++;
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @route("/pz/ajax/pages/sort", name="pzAjaxPagesSort")
     * @return Response
     */
    public function pzAjaxPagesSort()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $cat = $request->get('cat');
        $data = (array)json_decode($request->get('data'));

        foreach ($data as $itm) {
            /** @var Page $orm */
            $orm = Page::getById($pdo, $itm->id);

            $category = $orm->getCategory() ? (array)json_decode($orm->getCategory()) : array();
            if (!in_array($cat, $category)) {
                $category[] = $cat;
            }

            $categoryRank = $orm->getCategoryRank() ? (array)json_decode($orm->getCategoryRank()) : array();
            $categoryParent = $orm->getCategoryParent() ? (array)json_decode($orm->getCategoryParent()) : array();

            $categoryRank["cat{$cat}"] = $itm->rank;
            $categoryParent["cat{$cat}"] = $itm->parentId;

            $orm->setCategory(json_encode($category));
            $orm->setCategoryRank(json_encode($categoryRank));
            $orm->setCategoryParent(json_encode($categoryParent));
            $orm->save();
        }

        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/page/change", name="pzAjaxPageChange")
     * @return Response
     */
    public function pzAjaxPageChange()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $id = $request->get('id');
        $oldCat = $request->get('oldCat');
        $newCat = $request->get('newCat') ?: 0;

        $root = Extension::nestablePges(Page::data($pdo), $oldCat);
        $nodes = Tree::getChildrenAndSelfAsArray($root, $id);
        foreach ($nodes as $node) {
            /** @var Page $orm */
            $orm = $node->getExtras()['orm'];

            $category = $orm->getCategory() ? (array)json_decode($orm->getCategory()) : array();
            $category = array_filter($category, function ($itm) use ($oldCat) {
                return $oldCat != $itm;
            });
            if ($newCat != 0) {
                $category[] = $newCat;
            }

            $categoryRank = $orm->getCategoryRank() ? (array)json_decode($orm->getCategoryRank()) : array();
            $categoryParent = $orm->getCategoryParent() ? (array)json_decode($orm->getCategoryParent()) : array();

            $categoryRank["cat{$newCat}"] = $orm->getId() == $id ? 0 : $categoryRank["cat{$oldCat}"];
            $categoryParent["cat{$newCat}"] = $orm->getId() == $id ? 0 : $categoryParent["cat{$oldCat}"];

            unset($categoryRank["cat{$oldCat}"]);
            unset($categoryParent["cat{$oldCat}"]);

            $orm->setCategory(json_encode($category));
            $orm->setCategoryRank(json_encode($categoryRank));
            $orm->setCategoryParent(json_encode($categoryParent));
            $orm->save();
        }

        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/page/closed", name="pzAjaxPageClosed")
     * @return Response
     */
    public function pzAjaxPageClosed()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $id = $request->get('id');
        $cat = $request->get('cat');
        $closed = $request->get('closed') ?: 0;

        /** @var Page $orm */
        $orm = Page::getById($pdo, $id);
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $categoryClosed = $orm->getCategoryClosed() ? (array)json_decode($orm->getCategoryClosed()) : array();
        $categoryClosed["cat{$cat}"] = $closed;
        $orm->setCategoryClosed(json_encode($categoryClosed));
        $orm->save();

        return new Response('OK');
    }

    /**
     * @route("/pz/ajax/files", name="pzAjaxFiles")
     * @return Response
     */
    public function pzAjaxFiles()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();

        $currentFolderId = $request->get('currentFolderId') ?: 0;
        $keyword = $request->get('keyword');

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

        return new JsonResponse(array(
            'keyword' => $keyword,
            'files' => $data,
        ));
    }

    /**
     * @route("/pz/ajax/folders", name="pzAjaxFolders")
     * @return Response
     */
    public function pzAjaxFolders()
    {
        $request = Request::createFromGlobals();
        $currentFolderId = $request->get('currentFolderId') ?: 0;

        $root = $this->getFolderRoot($currentFolderId);

        return new JsonResponse(array(
            'root' => $root,
        ));
    }

    /**
     * @route("/pz/ajax/folder/nav", name="pzAjaxFolderNav")
     * @return Response
     */
    public function pzAjaxFolderNav()
    {
        $request = Request::createFromGlobals();
        $currentFolderId = $request->get('currentFolderId') ?: 0;

        $root = $this->getFolderRoot($currentFolderId);
        $path = $root->path($currentFolderId);

        return new JsonResponse(array(
            'currentFolder' => end($path),
            'path' => $path,
        ));
    }

    /**
     * @route("/pz/ajax/files/add/folder", name="pzAjaxAddFolder")
     * @return Response
     */
    public function pzAjaxAddFolder()
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
     * @route("/pz/ajax/files/edit/folder", name="pzAjaxEditFolder")
     * @return Response
     */
    public function pzAjaxEditFolder()
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
     * @route("/pz/ajax/folders/update", name="pzAjaxFoldersUpdate")
     * @return Response
     */
    public function pzAjaxFoldersUpdate()
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
     * @route("/pz/ajax/file/move", name="pzAjaxFileMove")
     * @return Response
     */
    public function pzAjaxFileMove()
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
     * @route("/pz/ajax/files/delete/folder", name="pzAjaxDeleteFolder")
     * @return Response
     */
    public function pzAjaxDeleteFolder()
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
     * @route("/pz/ajax/files/delete/file", name="pzAjaxDeleteFile")
     * @return Response
     */
    public function pzAjaxDeleteFile()
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
     * @route("/pz/ajax/files/upload", name="pzAjaxUpload")
     * @return Response
     */
    public function pzAjaxUpload()
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