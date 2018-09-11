<?php

namespace Pz\Twig;

use Pz\Orm\Page;
use Pz\Router\Node;
use Pz\Router\Tree;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('getenv', 'getenv'),
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'nestable' => new TwigFilter('nestable', array($this, 'nestable')),
            'nestablePges' => new TwigFilter('nestablePges', array($this, 'nestablePges')),
        );

    }

    /**
     * @param $orms
     * @return Node
     */
    public static function nestable($orms)
    {
        $nodes = array();
        foreach ($orms as $orm) {
            $nodes[] = new Node($orm->getId(), $orm->getTitle(), $orm->getParentId() ?: 0, $orm->getRank(), '', '', $orm->getStatus());
        }
        $tree = new Tree($nodes);
        return $tree->getRoot();
    }


    /**
     * @param Page[] $pages
     * @param $cat
     * @return Node
     */
    public static function nestablePges($pages, $cat)
    {
        $nodes = array();
        foreach ($pages as $page) {
            $category = json_decode($page->getCategory());
            if (!in_array($cat, $category) && !($cat == 0 && count($category) == 0)) {
                continue;
            }
            $categoryParent = (array)json_decode($page->getCategoryParent());
            $categoryRank = (array)json_decode($page->getCategoryRank());
            $categoryClosed = (array)json_decode($page->getCategoryClosed());

            $categoryParentValue = isset($categoryParent["cat$cat"]) ? $categoryParent["cat$cat"] : 0;
            $categoryRankValue = isset($categoryRank["cat$cat"]) ? $categoryRank["cat$cat"] : 0;
            $categoryClosedValue = isset($categoryClosed["cat$cat"]) ? $categoryClosed["cat$cat"] : 0;

            $node = new Node(
                $page->getId(),
                $page->getTitle(),
                $categoryParentValue,
                $categoryRankValue,
                $page->getUrl(),
                $page->objPageTempalte()->getId(),
                $page->getStatus(),
                $page->getAllowExtra(),
                $page->getMaxParams()
            );


            $node->setExtras(array(
                'orm' => $page,
                'model' => $page->getModel(),
                'closed' => $categoryClosedValue,
            ));

            $nodes[] = $node;
        }

        $tree = new Tree($nodes);
        return $tree->getRoot();
    }
}