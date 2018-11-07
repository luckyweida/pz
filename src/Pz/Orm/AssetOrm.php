<?php
//Last updated: 2018-11-04 10:14:44
namespace Pz\Orm;

class AssetOrm extends \Pz\Orm\Generated\AssetOrm
{
    public function objAsset() {
        return Asset::getById($this->getPdo(), $this->getTitle());
    }
}