<?php

namespace Pz\Db;


use Cocur\Slugify\Slugify;

class Walle
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function sync($pdo)
    {
        $rc = new \ReflectionClass(get_called_class());
        $slugify = new Slugify(['trim' => false]);
        $tableName = $slugify->slugify($rc->getShortName(), '_');

        $table = new Table($pdo, $tableName);
        $table->create();
        $table->sync(static::getFields());
    }

    public static function getFields()
    {
        $rc = new \ReflectionClass(get_called_class());
        $properties = $rc->getProperties();

        $result = array();
        foreach ($properties as $property) {
            $comment = $property->getDocComment();
            preg_match('/@pz(\ )+(.*)/', $comment, $matches);
            if (count($matches) == 3) {
                $result[$property->getName()] = $matches[2];
            }
        }
        return $result;
    }
}