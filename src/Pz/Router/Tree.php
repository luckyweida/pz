<?php

namespace Pz\Router;


class Tree
{
    private $nodes;

    /**
     * Tree constructor.
     * @param $nodes
     */
    public function __construct($nodes)
    {
        $this->nodes = $nodes;

        usort($nodes, function ($node1, $node2) {
            return $node1->getRank() - $node2->getRank();
        });
    }

    public function getNodeByUrl($url)
    {
        foreach ($this->nodes as $node) {
            if ($node->getUrl() == $url) {
                return $node;
            }
        }
        return null;
    }

    public function getRoot()
    {
        return static::_getRoot(new Node(0, 'root'));
    }

    public function _getRoot(Node $node)
    {
        foreach ($this->nodes as $itm) {
            if ($itm->getParentId() === $node->getId()) {
                $node->addChild($this->_getRoot($itm));
            }
        }
        return $node;
    }

}