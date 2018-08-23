<?php

namespace Pz\Router;


class Node
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $parentId;
    /**
     * @var int
     */
    private $rank;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $template;
    /**
     * @var int
     */
    private $status;
    /**
     * @var int
     */
    private $allowExtra;
    /**
     * @var int
     */
    private $maxParams;
    /**
     * @var array
     */
    private $extras = array();
    /**
     * @var array
     */
    private $children = array();

    /**
     * Node constructor.
     * @param $id
     * @param $title
     * @param string $parentId
     * @param int $rank
     * @param string $url
     * @param string $template
     * @param int $status
     * @param int $allowExtra
     * @param int $maxParams
     */
    public function __construct($id, $title, $parentId = '', $rank = 0, $url = '', $template = '', $status = 1, $allowExtra = 0, $maxParams = 0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->parentId = $parentId;
        $this->rank = $rank;
        $this->url = $url;
        $this->template = $template;
        $this->status = $status;
        $this->allowExtra = $allowExtra;
        $this->maxParams = $maxParams;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getParentId(): string
    {
        return $this->parentId;
    }

    /**
     * @param string $parentId
     */
    public function setParentId(string $parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getAllowExtra(): int
    {
        return $this->allowExtra;
    }

    /**
     * @param int $allowExtra
     */
    public function setAllowExtra(int $allowExtra)
    {
        $this->allowExtra = $allowExtra;
    }

    /**
     * @return int
     */
    public function getMaxParams(): int
    {
        return $this->maxParams;
    }

    /**
     * @param int $maxParams
     */
    public function setMaxParams(int $maxParams)
    {
        $this->maxParams = $maxParams;
    }

    /**
     * @return array
     */
    public function getExtras(): array
    {
        return $this->extras;
    }

    /**
     * @param array $extras
     */
    public function setExtras(array $extras)
    {
        $this->extras = $extras;
    }

    /**
     * @param Node $child
     */
    public function addExtra($key, $value)
    {
        $this->extras[$key] = $value;
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * @param Node $child
     */
    public function addChild(Node $child)
    {
        $this->children[] = $child;
    }

    public function contains($node)
    {
        if (!$node) {
            return 0;
        }
        return static::_contains($this, $node);
    }

    private static function _contains($parent, $child)
    {
        if ($parent->getId() == $child->getId()) {
            return 1;
        }
        foreach ($parent->getChildren() as $itm) {
            if (static::_contains($itm, $child)) {
                return 1;
            }
        }
        return 0;
    }

    public function hasActiveChildren()
    {
        foreach ($this->getChildren() as $itm) {
            if ($itm->getStatus() == 1) {
                return 1;
            }
        }
        return 0;
    }
}