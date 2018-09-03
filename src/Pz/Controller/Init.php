<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class Init extends Mo
{

    /**
     * @route("/pz_init", name="init")
     * @return Response
     */
    public function init(Connection $conn)
    {
//
//        $pdo = $conn->getWrappedConnection();
//        $orm = new _Model($pdo);
//        $orm;
    }

    public function getNodes()
    {
        return array();
    }
}