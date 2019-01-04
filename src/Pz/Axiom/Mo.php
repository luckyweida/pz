<?php

namespace Pz\Axiom;

use Doctrine\DBAL\Connection;
use Pz\Router\Tree;
use Pz\Service\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Mo extends Controller
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var PageService
     */
    protected $pageService;

    /**
     * @var Tree
     */
    protected $tree;

    /**
     * Mo constructor.
     * @param Connection $connection
     * @param PageService $pageService
     */
    public function __construct(Connection $connection, PageService $pageService)
    {
        $this->connection = $connection;
        $this->pageService = $pageService;
        $this->tree = new Tree($this->getNodes());
    }

    /**
     * @param $requestUri
     * @return array
     */
    function getParams($requestUri)
    {
        $fragments = explode('/', trim($requestUri, '/'));
        $args = array();
        $node = $this->tree->getNodeByUrl($requestUri . '/');
        if (!$node) {
            $node = $this->tree->getNodeByUrl($requestUri);
        }
        if (!$node) {
            for ($i = count($fragments), $il = 0; $i > $il; $i--) {
                $parts = array_slice($fragments, 0, $i);
                $node = $this->tree->getNodeByUrl('/' . implode('/', $parts) . '/');
                if (!$node) {
                    $node = $this->tree->getNodeByUrl('/' . implode('/', $parts));
                }
                if ($node) {
                    if ((!$node->getAllowExtra() && (count($fragments) - count($parts) == 0)) ||
                        ($node->getAllowExtra() && $node->getMaxParams() >= (count($fragments) - count($parts)))) {
                        $args = array_values(array_diff($fragments, $parts));
                        break;
                    } else {
                        throw new NotFoundHttpException();
                    }
                }
            }
        }
        if (!$node) {
            throw new NotFoundHttpException();
        }
        return array(
            'args' => $args,
            'fragments' => $fragments,
            'node' => $node,
            'root' => $this->tree->getRoot(),
            'returnUrl' => '',
        );
    }

    abstract function getNodes();

}