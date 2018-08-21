<?php

namespace Pz\Db;


use Cocur\Slugify\Slugify;

class Walle
{

    public static function sync($pdo)
    {
        $rc = new \ReflectionClass(get_called_class());
        $slugify = new Slugify(['trim' => false]);
        $tableName = $slugify->slugify($rc->getShortName(), '_');

        $table = new Table($pdo, $tableName);
        $table->create();
        $table->sync(static::fields());
    }

    public static function fields()
    {
        $rc = new \ReflectionClass(get_called_class());
        $properties = $rc->getProperties();

        $result = array();
        foreach ($properties as $property) {
            $comment = $property->getDocComment();
            preg_match('/@var(\ )+(.*)/', $comment, $matches);
            if (count($matches) == 3) {
                $result[$property->getName()] = $matches[2];
            }
        }
        return $result;
    }
}