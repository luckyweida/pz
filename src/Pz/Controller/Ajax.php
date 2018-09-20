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
        return new JsonResponse(json_decode('{"currentFolder":{"id":"-1","parentId":"-2","rank":"1","visible":1,"title":"Accommodation","twig":null,"url":"\/pz\/files\/?currentFolderId=-1","icon":null,"allowExtra":false,"maxParams":0},"keyword":"","path":[{"id":-1,"parentId":-2,"rank":0,"visible":1,"title":"Home","twig":"\/pz\/files\/?currentFolderId=-1","url":null,"icon":null,"allowExtra":false,"maxParams":0}],"files":[{"zdb":{},"title":"IMG_2391.JPG","description":"","isFolder":"0","fileName":"IMG_2391.JPG","fileType":"image\/jpeg","fileSize":"2522153","fileLocation":"126.JPG","__slug":"img-2391-jpg","__modelClass":"Asset","__rank":"-1","__parentId":"125","__added":"2 weeks ago","__modified":"2018-09-02 00:02:05","__active":"1","id":"126","track":"b6836a547950c06916fce106e9c97fe2"}]}'));
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
     * @route("/pz/ajax/folders", name="pzAjaxFolders")
     * @return Response
     */
    public function pzAjaxFolders()
    {
        $folderOpenMaxLimit = 10;

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $request = Request::createFromGlobals();
        $currentFolderId = $request->get('currentFolderId');

        $childrenCount = array();
        $nodes = array();

        /** @var Asset[] $data */
        $data = Asset::data($pdo, array('whereSql' => 'm.isFolder = 1'));
        foreach ($data as $itm) {
            if (!isset($childrenCount[$itm->getParentId()])) {
                $childrenCount[$itm->getParentId()] = 0;
            }
            $childrenCount[$itm->getParentId()]++;

            $node = new Node($itm->getId(), $itm->getTitle() ?: 'Home', $itm->getParentId() ?: 0, $itm->getRank());
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
        $root->setText('Home');
        $root->setState(array('opened' => true, 'selected' => false));
        if ($currentFolderId === 0) {
            $root->setStateValue('selected', 1);
        }
        return new JsonResponse($root);
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
}