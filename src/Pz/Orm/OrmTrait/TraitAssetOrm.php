<?php
//Last updated: 2019-01-02 17:26:21
namespace Pz\Orm\OrmTrait;

use Pz\Orm\Asset;

trait TraitAssetOrm
{
    public function objAsset() {
        return Asset::getById($this->getPdo(), $this->getTitle());
    }
}