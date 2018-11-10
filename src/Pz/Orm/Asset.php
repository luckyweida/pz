<?php
//Last updated: 2018-09-03 23:03:24
namespace Pz\Orm;

class Asset extends \Pz\Orm\Generated\Asset
{
    public function delete()
    {
        $result = AssetOrm::data($this->getPdo(), array(
            'whereSql' => 'm.title = ?',
            'params' => array($this->getId()),
        ));
        foreach ($result as $itm) {
            $itm->delete();
        }
        return parent::delete();
    }
}