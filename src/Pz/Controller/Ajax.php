<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class Ajax extends Mo
{

    /**
     * @route("/pz/ajax/model/sort", name="pzAjaxModelSort")
     * @return Response
     */
    public function ajaxModelSort(Connection $conn)
    {

        /** @var \PDO $pdo */
        $pdo = $conn->getWrappedConnection();

        $request = Request::createFromGlobals();
        $data = json_decode($request->get('data'));
        foreach ($data as $idx => $itm) {
            $orm = _Model::getById($pdo, $itm);
            if ($orm) {
                $orm->setRank($idx);
                $orm->save();
            }
        }
        return new Response('OK');
    }

    public function getNodes()
    {
        return array();
    }
}