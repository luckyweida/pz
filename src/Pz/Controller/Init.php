<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Pz\Orm\AssetSize;
use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Orm\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class Init extends Controller
{

    /**
     * @route("/init_pz", name="init")
     * @return Response
     */
    public function init()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $pdo->beginTransaction();

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
            $className::updateModel($pdo);
        }

        $pdo->commit();


        $pdo->beginTransaction();

        $orm = User::getByField($pdo, 'title', 'weida');
        if (!$orm) {
            $encoder = new MessageDigestPasswordEncoder();
            $orm = new User($pdo);
            $orm->setTitle('weida');
            $orm->setPassword($encoder->encodePassword('123', ''));
            $orm->setName('Weida');
            $orm->setEmail('luckyweida@gmail.com');
            $orm->save();
        }

        $orm = User::getByField($pdo, 'title', 'admin');
        if (!$orm) {
            $encoder = new MessageDigestPasswordEncoder();
            $orm = new User($pdo);
            $orm->setTitle('admin');
            $orm->setPassword($encoder->encodePassword('dasiyebushuo', ''));
            $orm->setName('Administrator');
            $orm->setEmail('');
            $orm->save();
        }

        $orm = AssetSize::getByField($pdo, 'title', 'full');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setTitle('full');
            $orm->setWidth(1440);
            $orm->save();
        }

        $orm = AssetSize::getByField($pdo, 'title', 'large');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setTitle('large');
            $orm->setWidth(960);
            $orm->save();
        }

        $orm = AssetSize::getByField($pdo, 'title', 'medium');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setTitle('medium');
            $orm->setWidth(480);
            $orm->save();
        }

        $orm = AssetSize::getByField($pdo, 'title', 'small');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setTitle('small');
            $orm->setWidth(240);
            $orm->save();
        }

        $orm = PageCategory::getByField($pdo, 'title', 'Main nav');
        if (!$orm) {
            $orm = new PageCategory($pdo);
            $orm->setTitle('Main nav');
            $orm->setCode('main');
            $orm->save();
        }

        $orm = PageCategory::getByField($pdo, 'title', 'Footer nav');
        if (!$orm) {
            $orm = new PageCategory($pdo);
            $orm->setTitle('Footer nav');
            $orm->setCode('footer');
            $orm->save();
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'layout.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setTitle('layout.twig');
            $orm->setFilename('layout.twig');
            $orm->save();
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'home.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setTitle('home.twig');
            $orm->setFilename('home.twig');
            $orm->save();
        }

        $pdo->commit();

        return new Response('OK');
    }

}