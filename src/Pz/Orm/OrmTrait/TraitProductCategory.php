<?php
//Last updated: 2019-01-02 17:26:31
namespace Pz\Orm\OrmTrait;

trait TraitProductCategory
{
    public function getChildren() :array {
        return static::data($this->getPdo(), array(
            'whereSql' => 'm.parentId = ?',
            'params' => array($this->getId()),
        ));
    }
}