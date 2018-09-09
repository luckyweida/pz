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
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('getenv', 'getenv'),
        );
    }

    public function getFilters()
    {
        return array(
            'nestable' => new TwigFilter('nestable', array($this, 'nestable')),
            'nestablePges' => new TwigFilter('nestablePges', array($this, 'nestablePges')),
        );

    }

    public function nestable($orms)
    {
        $nodes = array();
        foreach ($orms as $orm) {
            $nodes[] = new Node($orm->getId(), $orm->getTitle(), $orm->getParentId() ?: 0, $orm->getRank(), '', '', $orm->getStatus());
        }
        $tree = new Tree($nodes);
        return $tree->getRoot();
    }


    /**
     * @param Page[] $orms
     * @param $cat
     * @return Node
     */
    public function nestablePges($orms, $cat)
    {
        $nodes = array();
        foreach ($orms as $orm) {
            $category = json_decode($orm->getCategory());
            if (!in_array($cat, $category)) {
                continue;
            }
            $categoryParent = (array)json_decode($orm->getCategoryParent());
            $categoryRank = (array)json_decode($orm->getCategoryRank());

            $categoryParentValue = isset($categoryParent["cat$cat"]) ? $categoryParent["cat$cat"] : 0;
            $categoryRankValue = isset($categoryRank["cat$cat"]) ? $categoryRank["cat$cat"] : 0;

            $node = new Node(
                $orm->getId(),
                $orm->getTitle(),
                $categoryParentValue,
                $categoryRankValue,
                $orm->getUrl(),
                $orm->objPageTempalte()->getId(),
                $orm->getStatus(),
                $orm->getAllowExtra(),
                $orm->getMaxParams()
            );


            $node->setExtras(array(
                'model' => $orm->getModel(),
            ));

            $nodes[] = $node;

        }

        $tree = new Tree($nodes);
        return $tree->getRoot();
    }
}