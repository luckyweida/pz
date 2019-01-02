<?php
//Last updated: 2018-11-10 16:29:19
namespace Pz\Orm;

use Pz\Router\InterfaceNode;
use Pz\Router\TraitNode;

class ProductCategory extends \Pz\Orm\Generated\ProductCategory
{
    public function getChildren() {
        return static::data($this->getPdo(), array(
            'whereSql' => 'm.parentId = ?',
            'params' => array($this->getId()),
        ));
    }
}