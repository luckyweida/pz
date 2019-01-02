<?php

namespace Pz\Router;

trait TraitNodeExtras
{
    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * @param InterfaceNode $child
     */
    public function addChild(InterfaceNode $child)
    {
        $this->children[] = $child;
    }

    /**
     * @param InterfaceNode $node
     * @return int
     */
    public function contains(InterfaceNode $node)
    {
        if (!$node) {
            return 0;
        }
        return static::_contains($this, $node);
    }

    /**
     * @param InterfaceNode $parent
     * @param InterfaceNode $child
     * @return int
     */
    private static function _contains(InterfaceNode $parent, InterfaceNode $child)
    {
        if ($parent->getId() == $child->getId()) {
            return 1;
        }
        foreach ($parent->getChildren() as $itm) {
            if (static::_contains($itm, $child)) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    public function hasActiveChildren()
    {
        foreach ($this->getChildren() as $itm) {
            if ($itm->getStatus() == 1) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @param $needleId
     * @return array|bool
     */
    public function path($needleId)
    {
        return static::_path($this, $needleId);
    }

    /**
     * @param InterfaceNode $node
     * @param $needleId
     * @return array|bool
     */
    private static function _path(InterfaceNode $node, $needleId)
    {
        $n = clone $node;
        $n->setChildren(array());
        $result = array($n);

        if ($node->getId() == $needleId) {
            return $result;
        }
        foreach ($node->getChildren() as $itm) {
            $r = static::_path($itm, $needleId);
            if ($r !== false) {
                return array_merge($result, $r);
            }
        }
        return false;
    }
}