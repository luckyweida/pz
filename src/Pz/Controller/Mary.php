<?php

namespace Pz\Controller;


use Pz\Router\Mo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class Mary extends Mo
{

    /**
     * @route("/{page}", requirements={"page" = ".*"}, name="mary")
     * @return Response
     */
    public function mary()
    {
        $request = Request::createFromGlobals();
        $requestUri = $request->getRequestUri();
        if ($requestUri == '/pz') {
            return $this->redirect('/pz/dashboard');
        }

        var_dump($request->getRequestUri());
        var_dump('web');
        exit;
    }

    public function getNodes()
    {

    }
}