<?php

namespace Pz\Service;

use Pz\Form\Type\ContentBlock;
use Pz\Orm\FragmentBlock;
use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Router\Node;
use Pz\Router\Tree;

class Utils
{
    /**
     * Db constructor.
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

            $nodes = array();
            foreach ($pages as $itm) {
                $categoryParent = !$itm->getCategoryParent() ? array() : (array)json_decode($itm->getCategoryParent());
                $categoryRank = !$itm->getCategoryRank() ? array() : (array)json_decode($itm->getCategoryRank());
                $parent = isset($categoryParent['cat' . $category->getId()]) ? $categoryParent['cat' . $category->getId()] : 0;
                $rank = isset($categoryRank['cat' . $category->getId()]) ? $categoryRank['cat' . $category->getId()] : 0;

                $node = new Node($itm->getId(), $itm->getTitle(), $parent, $rank, $itm->getUrl(), $itm->objPageTempalte()->getFilename(), $itm->getStatus(), $itm->getAllowExtra(), $itm->getMaxParams());
//                $node->objContent = $itm->objContent();
                $nodes[] = $node;
            }

            $tree = new Tree($nodes);
            $result = $tree->getRoot();
        }

        return $result;
    }

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

    public function getUniqId() {
        return uniqid();
    }
}