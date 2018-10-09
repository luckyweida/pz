<?php

namespace Pz\Router;


class Node implements \JsonSerializable
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
     * @var string|null
     */
    private $url;
    /**
     * @var string|null
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
     * @var string|null
     */
    private $text;
    /**
     * @var array
     */
    private $state = array();

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
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return null|string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param null|string $template
     */
    public function setTemplate($template)
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

    /**
     * @return null|string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param null|string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * @param array $state
     */
    public function setState(array $state)
    {
        $this->state = $state;
    }

    /**
     * @param $idx
     * @param $value
     */
    public function setStateValue($idx, $value)
    {
        $this->state[$idx] = $value;
    }

    /**
     * @param $node
     * @return int
     */
    public function contains($node)
    {
        if (!$node) {
            return 0;
        }
        return static::_contains($this, $node);
    }

    public function path($needleId)
    {
        return static::_path($this, $needleId);
    }

    private static function _path(Node $node, $needleId)
    {
        $n = clone $node;
        $n->setChildren(array());
        $result = array($n);

        if ($node->getId() == $needleId) {
            return $result;
        }
        foreach ($node->getChildren() as $itm) {
            $r = static::_path($itm, $needleId);
            if ($r !== false) {
                return array_merge($result, $r);
            }
        }
        return false;
    }

    /**
     * @param $parent
     * @param $child
     * @return int
     */
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

    /**
     * @return int
     */
    public function hasActiveChildren()
    {
        foreach ($this->getChildren() as $itm) {
            if ($itm->getStatus() == 1) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->id = $this->getId();
        $obj->title = $this->getTitle();
        $obj->parentId = $this->getParentId();
        $obj->rank = $this->getRank();
        $obj->url = $this->getUrl();
        $obj->template = $this->getTemplate();
        $obj->status = $this->getStatus();
        $obj->allowExtra = $this->getAllowExtra();
        $obj->maxParams = $this->getMaxParams();
        $obj->extras = $this->getExtras();
        $obj->children = $this->getChildren();
        $obj->text = $this->getText();
        $obj->state = $this->getState();
        return $obj;
    }
}