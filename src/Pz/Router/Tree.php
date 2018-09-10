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
        usort($nodes, function ($node1, $node2) {
            return $node1->getRank() - $node2->getRank();
        });
        $this->nodes = $nodes;
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

    public static function getChildrenAndSelfAsArray($root, $needleId)
    {
        return static::_getChildrenAndSelfAsArray($root, $needleId, 0);
    }

    private static function _getChildrenAndSelfAsArray(Node $node, $needleId, $added)
    {
        $result = array();
        if ($node->getId() == $needleId || $added) {
            $added = 1;
            $result[] = $node;
        }
        foreach ($node->getChildren() as $itm) {
            $r = static::_getChildrenAndSelfAsArray($itm, $needleId, $added);
            if ($added || count($r) > 0) {
                $result = array_merge($result, $r);
            }
        }
        return $result;
    }
}