<?php

namespace Pz\Router;


interface InterfaceNode
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getParentId();

    /**
     * @return mixed
     */
    public function getRank();

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @param InterfaceNode $child
     * @return mixed
     */
    public function addChild(InterfaceNode $child);

    /**
     * @return mixed
     */
    public function getChildren();

    /**
     * @param array $children
     * @return mixed
     */
    public function setChildren(array $children);
}