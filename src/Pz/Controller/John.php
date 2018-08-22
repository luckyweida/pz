<?php

namespace Pz\Controller;

use Pz\Router\Mo;
use Pz\Router\Node;
use Symfony\Component\Routing\Annotation\Route;

class John extends Mo
{

    /**
     * @route("/pz/{page}", requirements={"page" = ".*"}, name="john")
     * @return Response
     */
    public function john()
    {
        define('PROJECT', '');
        define('CDN', 'http://hhcode.com');
        define('DEBUG_ENABLED', 1);

        $loader = $this->container->get('twig')->getLoader();
        $loader->addPath(__DIR__ . '/../../../templates/');

        $params = $this->getParams();
        return $this->render($params['node']->getTemplate(), $params);
    }

    public function getNodes()
    {
        $nodes = array();
        $nodes[] = new Node(1, 'Pages', 0, 0, array('icon' => 'fa fa-sitemap'), '/pz/pages', 'pages.twig');
        $nodes[] = new Node(2, 'Database', 0, 1, array('icon' => 'fa fa-database'));
        $nodes[] = new Node(3, 'Files', 0, 2, array('icon' => 'fa file-image-o'), '/pz/files', 'files.twig');
        $nodes[] = new Node(4, 'Admin', 0, 3, array('icon' => 'fa fa-cogs'));
        $nodes[] = new Node(11, 'Page', 1, 0, array(), '/pz/pages/detail', 'page.twig', 2, 1, 1);
        $nodes[] = new Node(41, 'Customised Models', 4, 0, array(), '/pz/models/customised', 'models.twig');
        $nodes[] = new Node(411, 'Customised Model', 41, 0, array(), '/pz/models/customised/detail', 'model.twig', 2, 1, 1);
        $nodes[] = new Node(42, 'Built-in Models', 4, 1, array(), '/pz/models/built-in', 'models.twig', 2, 1, 1);
        $nodes[] = new Node(421, 'Built-in Model', 42, 0, array(), '/pz/models/built-in/detail', 'model.twig', 2, 1, 1);
        return $nodes;
    }
}