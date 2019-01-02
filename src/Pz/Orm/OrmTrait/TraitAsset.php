<?php
//Last updated: 2019-01-02 17:25:06
namespace Pz\Orm\OrmTrait;

trait TraitAsset
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