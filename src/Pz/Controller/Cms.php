<?php

namespace Pz\Controller;

use Pz\Orm\_Model;
use Pz\Axiom\Mo;
use Pz\Router\Node;
use Pz\Service\Db;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class Cms extends Mo
{
    /**
     * @route("/pz/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('pz/login.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @route("/pz/{page}", requirements={"page" = ".*"}, name="cms")
     * @return Response
     */
    public function cms()
    {
        $params = $this->getParams();
        return $this->render($params['node']->getTemplate(), $params);
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        $nodes = array();
        $nodes[] = new Node(1, 'Pages', 0, 0, '/pz/pages', 'pz/pages.twig');
        $nodes[] = new Node(2, 'Database', 0, 1);
        $nodes[] = new Node(3, 'Files', 0, 2, '/pz/files', 'pz/files.twig');
        $nodes[] = new Node(4, 'Admin', 0, 3);
        $nodes[] = new Node(11, 'Page', 1, 0, '/pz/pages/detail', 'page.twig', 2, 1, 1);
        $nodes[] = new Node(41, 'Customised Models', 4, 998, '/pz/admin/models/customised', 'pz/models.twig');
        $nodes[] = new Node(411, 'Customised Model', 41, 0, '/pz/admin/models/customised/detail', 'pz/model.twig', 2, 1, 1);
        $nodes[] = new Node(42, 'Built-in Models', 4, 999, '/pz/admin/models/built-in', 'pz/models.twig');
        $nodes[] = new Node(421, 'Built-in Model', 42, 0, '/pz/admin/models/built-in/detail', 'pz/model.twig', 2, 1, 1);

        $nodes[0]->addExtra('icon', 'fa fa-sitemap');
        $nodes[1]->addExtra('icon', 'fa fa-database');
        $nodes[2]->addExtra('icon', 'fa fa-file-image-o');
        $nodes[3]->addExtra('icon', 'fa fa-cogs');

        $nodes[] = new Node(412, 'Sync Model', 41, 0, '/pz/admin/models/customised/sync', 'pz/model-sync.twig', 2, 1, 1);
        $nodes[] = new Node(422, 'Sync Model', 42, 0, '/pz/admin/models/built-in/sync', 'pz/model-sync.twig', 2, 1, 1);

        $db = new Db($this->connection);

        /** @var _Model[] $modelDatabase */
        $modelDatabase = $db->active('_Model');
        foreach ($modelDatabase as $idx => $itm) {
            if ($itm->getDataType() == 0) {
                $nodes[] = new Node('2-' . $itm->getId(), $itm->getTitle(), 2, $idx, "/pz/database/" . $itm->getId(), 'pz/contents.twig');
                $nodes[] = new Node('2-' . $itm->getId() . '-1', $itm->getTitle(), '2-' . $itm->getId(), 0, "/pz/database/" . $itm->getId() . '/detail', 'pz/content.twig', 2, 1, 1);
            } else if ($itm->getDataType() == 1) {
                $nodes[] = new Node('4-' . $itm->getId(), $itm->getTitle(), 4, $idx, "/pz/admin/" . $itm->getId(), 'pz/contents.twig');
                $nodes[] = new Node('4-' . $itm->getId() . '-1', $itm->getTitle(), '4-' . $itm->getId(), 0, "/pz/admin/" . $itm->getId() . '/detail', 'pz/content.twig', 2, 1, 1);
            }
        }

        return $nodes;
    }
}