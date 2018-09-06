<?php

namespace Pz\Twig;

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
}