<?php

namespace Pz\Controller;


use Pz\Axiom\Mo;
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

        return $this->render('layout.twig');
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
        return array();
    }
}