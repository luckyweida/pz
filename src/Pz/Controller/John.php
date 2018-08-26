<?php

namespace Pz\Controller;

use Doctrine\DBAL\Connection;
use Pz\Orm\_Model;
use Pz\Router\Mo;
use Pz\Router\Node;
use Pz\Service\Db;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class John extends Mo
{
    /**
     * @route("/pz/{page}", requirements={"page" = ".*"}, name="john")
     * @return Response
     */
    public function john()
    {
        $params = $this->getParams();
        return $this->render($params['node']->getTemplate(), $params);
    }

    public function getNodes()
    {
        $nodes = array();
        $nodes[] = new Node(1, 'Pages', 0, 0, '/pz/pages', 'pz/pages.twig');
        $nodes[] = new Node(2, 'Database', 0, 1);
        $nodes[] = new Node(3, 'Files', 0, 2, '/pz/files', 'pz/files.twig');
        $nodes[] = new Node(4, 'Admin', 0, 3);
        $nodes[] = new Node(11, 'Page', 1, 0, '/pz/pages/detail', 'page.twig', 2, 1, 1);
        $nodes[] = new Node(41, 'Customised Models', 4, 0, '/pz/admin/models/customised', 'pz/models.twig');
        $nodes[] = new Node(411, 'Customised Model', 41, 0, '/pz/admin/models/customised/detail', 'pz/model.twig', 2, 1, 1);
        $nodes[] = new Node(42, 'Built-in Models', 4, 1, '/pz/admin/models/built-in', 'pz/models.twig');
        $nodes[] = new Node(421, 'Built-in Model', 42, 0, '/pz/admin/models/built-in/detail', 'pz/model.twig', 2, 1, 1);

        $nodes[0]->addExtra('icon', 'fa fa-sitemap');
        $nodes[1]->addExtra('icon', 'fa fa-database');
        $nodes[2]->addExtra('icon', 'fa fa-file-image-o');
        $nodes[3]->addExtra('icon', 'fa fa-cogs');

        $nodes[] = new Node(412, 'Sync Model', 41, 0, '/pz/admin/models/customised/sync', 'pz/model-sync.twig', 2, 1, 1);
        $nodes[] = new Node(422, 'Sync Model', 42, 0, '/pz/admin/models/built-in/sync', 'pz/model-sync.twig', 2, 1, 1);

        $db = new Db($this->connection);

        /** @var _Model[] $modelDatabase */
        $modelDatabase = $db->data('_Model', array(
            'whereSql' => 'm.dataType = ?',
            'params' => array(0),
        ));
        foreach ($modelDatabase as $idx => $itm) {
            $nodes[] = new Node('2-' . $itm->getId(), $itm->getTitle(), 2, $idx, '/pz/database/' . $itm->getId(), 'pz/contents.twig');
            $nodes[] = new Node('2-' . $itm->getId() . '-1', $itm->getTitle(), '2-' . $itm->getId(), $idx, '/pz/database/' . $itm->getId() . '/detail', 'pz/content.twig', 2, 1, 1);
        }


        return $nodes;
    }
}