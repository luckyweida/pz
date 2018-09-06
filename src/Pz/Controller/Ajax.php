<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Service\Db;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}