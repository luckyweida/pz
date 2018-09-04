<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Pz\Orm\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


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

        foreach ($files as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'Generated')
            {
                continue;
            }

            $className = "Pz\\Orm\\" . rtrim($file, '.php');
            $serialisedModel = $className::getModel($pdo);
            if ($serialisedModel) {
                $serialisedModel->save(true);
            }
        }

        $encoder = new MessageDigestPasswordEncoder();
        $orm = new User($pdo);
        $orm->setTitle('weida');
        $orm->setPassword($encoder->encodePassword('123', ''));
        $orm->setName('Weida');
        $orm->setEmail('luckyweida@gmail.com');
        $orm->save();

        return new Response('OK');
    }

    public function getNodes()
    {
        return array();
    }
}