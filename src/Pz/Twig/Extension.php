<?php

namespace Pz\Twig;

use Pz\Orm\Page;
use Pz\Redirect\RedirectException;
use Pz\Router\Node;
use Pz\Router\Tree;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Extension constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            'getenv' => new TwigFunction('getenv', 'getenv'),
            'css' => new TwigFunction('css', array($this, 'css')),
            'redirect' => new TwigFunction('redirect', [$this, 'throwRedirectException']),
            'not_found' => new TwigFunction('not_found', [$this, 'throwNotFoundException']),
            'http_exception' => new TwigFunction('http_exception', [$this, 'throwHttpException'])
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

    public function css($path)
    {
//        while (@ob_end_clean());
//        var_dump($this->container->getParameter('kernel.project_dir') . '/public/' . $path);exit;
        return file_get_contents($this->container->getParameter('kernel.project_dir') . '/public/' . $path);
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
            $category = $page->getCategory() ? json_decode($page->getCategory()) : [];
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

    /**
     * @param $status
     * @param $message
     */
    public function throwHttpException($status = Response::HTTP_INTERNAL_SERVER_ERROR, $message)
    {
        throw new HttpException($status, $message);
    }

    /**
     * @param $status
     * @param $location
     */
    public function throwRedirectException($status = Response::HTTP_FOUND, $location)
    {
        throw new RedirectException($location, $status);
    }

    /**
     * @param $message
     */
    public function throwNotFoundException($message)
    {
        throw new NotFoundHttpException($message);
    }
}