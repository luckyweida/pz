<?php

namespace Pz\Service;

class Db
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
     * @param $className
     * @return mixed
     */
    public function create($className)
    {
        $fullName = "Web\\Orm\\$className";
        if (!class_exists($fullName)) {
            $fullName = "Pz\\Orm\\$className";
        }

        $pdo = $this->connection->getWrappedConnection();
        return new $fullName($pdo);
    }

    /**
     * @param $className
     * @param $id
     * @return mixed
     */
    public function getById($className, $id)
    {
        return $this->data($className, array(
            'whereSql' => 'm.id = ?',
            'params' => array($id),
            'oneOrNull' => 1,
        ));
    }

    /**
     * @param $className
     * @param $field
     * @param $value
     * @return mixed
     */
    public function getByField($className, $field, $value)
    {
        return $this->data($className, array(
            'whereSql' => "m.$field = ?",
            'params' => array($value),
            'oneOrNull' => 1,
        ));
    }

    /**
     * @param $className
     * @param array $options
     * @return mixed
     */
    public function active($className, $options = array())
    {
        if (isset($options['whereSql'])) {
            $options['whereSql'] .= ($options['whereSql'] ? ' AND ' : '') . 'm.active = 1';
        } else {
            $options['whereSql'] = 'm.active = 1';
        }
        return static::data($className, $options);
    }

    /**
     * @param $className
     * @param array $options
     * @return mixed
     */
    public function data($className, $options = array())
    {
        $fullName = "Web\\Orm\\$className";
        if (!class_exists($fullName)) {
            $fullName = "Pz\\Orm\\$className";
        }

        $pdo = $this->connection->getWrappedConnection();
        return $fullName::data($pdo, $options);
    }
}