<?php

namespace Pz\Controller;


use Pz\Axiom\Mo;
use Pz\Orm\Page;
use Pz\Router\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class Web extends Mo
{

    /**
     * @route("/{page}", requirements={"page" = ".*"}, name="web")
     * @return Response
     */
    public function web()
    {
        $request = Request::createFromGlobals();
        $requestUri = rtrim($request->getPathInfo(), '/');
        $params = $this->getParams($requestUri);
        return $this->render($params['node']->getTemplate(), $params);
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        $nodes = array();

        /** @var \PDO $pdo */
        $pdo = $this->connection->getWrappedConnection();

        /** @var Page[] $pages */
        $pages = Page::data($pdo, array(
            'whereSql' => 'm.status = 1',
        ));
        foreach ($pages as $itm) {
            $nodes[] = new Node($itm->getId(), $itm->getTitle(), 0, $itm->getRank(), $itm->getUrl(), $itm->objPageTempalte()->getFilename(), $itm->getStatus(), $itm->getAllowExtra(), $itm->getMaxParams());
        }

        return $nodes;
    }
}