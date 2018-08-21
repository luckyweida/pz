<?php

namespace Pz\Orm;

use Web\Db\Walle;

/**
 * Class _Model
 * @package Web\Orm
 */
class _Model extends Walle
{
    /**
     * @var varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    public $title;

    /**
     * @var varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    public $tableName;

    /**
     * @var varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    public $className;

    /**
     * @var varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    public $namespace;

    /**
     * @var tinyint(1) NOT NULL
     */
    public $modelType;

    /**
     * @var tinyint(1) NOT NULL
     */
    public $dataType;

    /**
     * @var tinyint(1) NOT NULL
     */
    public $listType;

    /**
     * @var smallint(6) NOT NULL
     */
    public $numberPerPage;

    /**
     * @var varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    public $defaultSortBy;

    /**
     * @var tinyint(1) NOT NULL
     */
    public $defaultOrder;

    /**
     * @var text COLLATE utf8mb4_unicode_ci NOT NULL
     */
    public $columnsJson;
}