<?php

namespace Pz\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PageService
{
    /**
     * PageService constructor.
     * @param Container $container
     * @param $pageClass
     */
    public function __construct(Container $container, $pageClass)
    {
        $this->container = $container;
        $this->pageClass = $pageClass;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPages()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');

        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        return $this->pageClass::data($pdo, array(
            'whereSql' => 'm.status != 0',
        ));
    }
}