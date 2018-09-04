<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class Init extends Mo
{

    /**
     * @route("/init_pz", name="init")
     * @return Response
     */
    public function init(Connection $conn)
    {

        /** @var \PDO $pdo */
        $pdo = $conn->getWrappedConnection();

        $dir = $this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/src/Pz/Orm';
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'Generated')
            {
                continue;
            }

            $className = "Pz\\Orm\\" . rtrim($file, '.php');
            $className::sync($pdo);
        }

        return new Response('OK');
    }

    public function getNodes()
    {
        return array();
    }
}