<?php

namespace Pz\Orm;

use Pz\Db\Walle;

/**
 * Class _Model
 * @package Web\Orm
 */
class _Model extends Walle
{

    /**
     * @pz int(11) NOT NULL AUTO_INCREMENT
     */
    private $id;

    /**
     * @pz varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $_slug;

    /**
     * @pz int(11) DEFAULT NULL
     */
    private $_rank;

    /**
     * @pz datetime DEFAULT NULL
     */
    private $_added;

    /**
     * @pz datetime DEFAULT NULL
     */
    private $_modified;

    /**
     * @pz tinyint(1) NOT NULL
     */
    private $_active;

    /**
     * @pz varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $title;

    /**
     * @pz varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $tableName;

    /**
     * @pz varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $className;

    /**
     * @pz varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $namespace;

    /**
     * @pz tinyint(1) NOT NULL
     */
    private $modelType;

    /**
     * @pz tinyint(1) NOT NULL
     */
    private $dataType;

    /**
     * @pz tinyint(1) NOT NULL
     */
    private $listType;

    /**
     * @pz smallint(6) NOT NULL
     */
    private $numberPerPage;

    /**
     * @pz varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $defaultSortBy;

    /**
     * @pz tinyint(1) NOT NULL
     */
    private $defaultOrder;

    /**
     * @pz text COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $columnsJson;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->_slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->_rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->_rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getAdded()
    {
        return $this->_added;
    }

    /**
     * @param mixed $added
     */
    public function setAdded($added)
    {
        $this->_added = $added;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->_modified;
    }

    /**
     * @param mixed $modified
     */
    public function setModified($modified)
    {
        $this->_modified = $modified;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->_active = $active;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getModelType()
    {
        return $this->modelType;
    }

    /**
     * @param mixed $modelType
     */
    public function setModelType($modelType)
    {
        $this->modelType = $modelType;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * @return mixed
     */
    public function getListType()
    {
        return $this->listType;
    }

    /**
     * @param mixed $listType
     */
    public function setListType($listType)
    {
        $this->listType = $listType;
    }

    /**
     * @return mixed
     */
    public function getNumberPerPage()
    {
        return $this->numberPerPage;
    }

    /**
     * @param mixed $numberPerPage
     */
    public function setNumberPerPage($numberPerPage)
    {
        $this->numberPerPage = $numberPerPage;
    }

    /**
     * @return mixed
     */
    public function getDefaultSortBy()
    {
        return $this->defaultSortBy;
    }

    /**
     * @param mixed $defaultSortBy
     */
    public function setDefaultSortBy($defaultSortBy)
    {
        $this->defaultSortBy = $defaultSortBy;
    }

    /**
     * @return mixed
     */
    public function getDefaultOrder()
    {
        return $this->defaultOrder;
    }

    /**
     * @param mixed $defaultOrder
     */
    public function setDefaultOrder($defaultOrder)
    {
        $this->defaultOrder = $defaultOrder;
    }

    /**
     * @return mixed
     */
    public function getColumnsJson()
    {
        return $this->columnsJson;
    }

    /**
     * @param mixed $columnsJson
     */
    public function setColumnsJson($columnsJson)
    {
        $this->columnsJson = $columnsJson;
    }


}