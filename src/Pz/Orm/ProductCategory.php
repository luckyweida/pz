<?php
//Last updated: 2018-11-10 16:29:19
namespace Pz\Orm;

class ProductCategory extends \Pz\Orm\Generated\ProductCategory
{
    public function getChildren() {
        return static::data($this->getPdo(), array(
            'whereSql' => 'm.parentId = ?',
            'params' => array($this->getId()),
        ));
    }

    public function getIdAndDescendantIds() {
        $result = array($this->getId());
        $decendants = $this->getDescendants();
        return array_merge($result, array_unique(array_map(function ($itm) {
            return $itm->getId();
        }, $decendants)));
    }

    public function getDescendantIds() {
        $decendants = $this->getDescendants();
        return array_unique(array_map(function ($itm) {
            return $itm->getId();
        }, $decendants));
    }

    public function getDescendants() {
        return static::_getDescendants($this);
    }

    static public function _getDescendants($category) {
        $result = array();

        /** @var ProductCategory[] $children */
        $children = $category->getChildren();
        foreach ($children as $child) {
            $result[] = $child;
            $result = array_merge($result, static::_getDescendants($child));
        }
        return $result;
    }

    public function hasSelected($selectedCategories) {
        return static::_hasSelected($this, $selectedCategories ?: array());
    }

    static public function _hasSelected($category, $selectedCategories) {
        $result = false;

        /** @var ProductCategory[] $children */
        $children = $category->getChildren();
        foreach ($children as $child) {
            if (in_array($child->getSlug(), $selectedCategories)) {
                $result = true;
            }
            if (!$result) {
                $r = static::_hasSelected($child, $selectedCategories);
                if ($r) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}