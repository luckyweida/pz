<?php

namespace Pz\Router;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Mo extends Controller
{
    private $tree;

    /**
     * Mo constructor.
     */
    public function __construct()
    {
        $this->tree = new Tree($this->getNodes());
    }

    function getParams()
    {
        $request = Request::createFromGlobals();
        $requestUri = rtrim($request->getPathInfo(), '/');
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
                if ($node && (
                        (!$node->getAllowExtra() && (count($fragments) - count($parts) == 0)) ||
                        ($node->getAllowExtra() && $node->getMaxParams() >= (count($fragments) - count($parts)))
                    )) {
                    $args = array_values(array_diff($fragments, $parts));
                    break;
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