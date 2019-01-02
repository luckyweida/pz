<?php

namespace Pz\Service;

use Pz\Form\Type\ContentBlock;
use Pz\Orm\FragmentBlock;
use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Router\Tree;

class UtilsService
{
    /**
     * UtilsService constructor.
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $categoryCode
     * @return null
     */
    public function nav($categoryCode)
    {
        /** @var \PDO $pdo */
        $pdo = $this->connection->getWrappedConnection();

        $result = null;
        /** @var PageCategory $category */
        $category = PageCategory::getByField($pdo, 'code', $categoryCode);
        if ($category) {
            /** @var \Pz\Orm\Page[] $pages */
            $pages = \Pz\Orm\Page::data($pdo, array(
                'whereSql' => 'm.category LIKE ? ',
                'params' => array('%"' . $category->getId() . '"%'),
            ));

            foreach ($pages as $itm) {
                $categoryParent = !$itm->getCategoryParent() ? array() : (array)json_decode($itm->getCategoryParent());
                $categoryRank = !$itm->getCategoryRank() ? array() : (array)json_decode($itm->getCategoryRank());

                $parent = isset($categoryParent['cat' . $category->getId()]) ? $categoryParent['cat' . $category->getId()] : 0;
                $rank = isset($categoryRank['cat' . $category->getId()]) ? $categoryRank['cat' . $category->getId()] : 0;

                $itm->setParentId($parent);
                $itm->setRank($rank);
            }

            $tree = new Tree($pages);
            $result = $tree->getRoot();
        }

        return $result;
    }

    /**
     * @return FragmentBlock[]
     */
    public function getBlockDropdownOptions()
    {
        /** @var \PDO $pdo */
        $pdo = $this->connection->getWrappedConnection();

        /** @var FragmentBlock[] $blocks */
        $blocks = FragmentBlock::active($pdo);
        foreach ($blocks as $block) {
            $items = json_decode($block->getItems());
            foreach ($items as &$item) {
                $choices = array();
                if ($item->widget == 9 || $item->widget == 10) {
                    $stmt = $pdo->prepare($item->sql);
                    $stmt->execute();
                    foreach ($stmt->fetchAll() as $key => $val) {
                        $choices[$val['key']] = $val['value'];
                    }
                }
                $item->choices = $choices;
            }
            $block->setItems(json_encode($items));
        }
        return $blocks;
    }

    /**
     * @return array
     */
    public function getBlockWidgets()
    {
        return array(
            0 => 'Text',
            1 => 'Textarea',
            2 => 'Asset picker',
            3 => 'Asset folder picker',
            4 => 'Checkbox',
            5 => 'Wysiwyg',
            6 => 'Date',
            7 => 'Datetime',
            8 => 'Time',
            9 => 'Choice',
            10 => 'Choice multi json',
            11 => 'Placeholder',
            12 => 'Read only text',
        );
    }

    /**
     * @return string
     */
    public function getUniqId() {
        return uniqid();
    }
}