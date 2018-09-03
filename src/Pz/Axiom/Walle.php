<?php

namespace Pz\Axiom;

use Cocur\Slugify\Slugify;

class Walle
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * #pz int(11) NOT NULL AUTO_INCREMENT
     */
    private $id;

    /**
     * #pz varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL
     */
    private $slug;

    /**
     * #pz int(11) NOT NULL DEFAULT 0
     */
    private $rank;

    /**
     * #pz datetime NOT NULL
     */
    private $added;

    /**
     * #pz datetime NOT NULL
     */
    private $modified;

    /**
     * #pz tinyint(1) NOT NULL DEFAULT 0
     */
    private $active;

    /**
     * Walle constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;

        $this->rank = 0;
        $this->added = date('Y-m-d H:i:s');
        $this->modified = date('Y-m-d H:i:s');
        $this->active = 1;
    }

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
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param mixed $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param mixed $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @param $pdo
     */
    public static function sync($pdo)
    {
        $tableName = static::getTableName();

        $eve = new Eve($pdo, $tableName);
        $eve->create();
        $eve->sync(static::getFields());
    }

    /**
     * @return array
     */
    public static function getFields()
    {
        $result = array();
        $rc = static::getReflectionClass();
        do {
            $result = array_merge($rc->getProperties(), $result);
            $rc = $rc->getParentClass();
        } while ($rc);
        return static::propertiesToFields($result);
    }

    /**
     * @return array
     */
    public static function getParentFields()
    {
        $rc = new \ReflectionClass(__CLASS__);
        return static::propertiesToFields($rc->getProperties());
    }

    /**
     * @param $properties
     * @return array
     */
    private static function propertiesToFields($properties)
    {
        $result = array();
        foreach ($properties as $property) {
            $comment = $property->getDocComment();
            preg_match('/#pz(\ )+(.*)/', $comment, $matches);
            if (count($matches) == 3) {
                $result[$property->getName()] = $matches[2];
            }
        }
        return $result;
    }

    /**
     * @param \PDO $pdo
     * @param $id
     * @return array|null
     */
    public static function getByField(\PDO $pdo, $field, $value)
    {
        return static::data($pdo, array(
            'whereSql' => "m.$field = ?",
            'params' => array($value),
            'oneOrNull' => 1,
        ));
    }

    /**
     * @param \PDO $pdo
     * @param $id
     * @return array|null
     */
    public static function getById(\PDO $pdo, $id)
    {
        return static::data($pdo, array(
            'whereSql' => 'm.id = ?',
            'params' => array($id),
            'oneOrNull' => 1,
        ));
    }

    /**
     * @param \PDO $pdo
     * @param array $options
     * @return array|null
     */
    public static function data(\PDO $pdo, $options = array())
    {
        $options['select'] = isset($options['select']) && !empty($options['select']) ? $options['select'] : 'm.*';
        $options['joins'] = isset($options['joins']) && !empty($options['joins']) ? $options['joins'] : null;
        $options['whereSql'] = isset($options['whereSql']) && !empty($options['whereSql']) ? "({$options['whereSql']})" : null;
        $options['params'] = isset($options['params']) && gettype($options['params']) == 'array' && count($options['params']) ? $options['params'] : [];
        $options['sort'] = isset($options['sort']) && !empty($options['sort']) ? $options['sort'] : 'm.rank';
        $options['order'] = isset($options['order']) && !empty($options['order']) ? $options['order'] : 'ASC';
        $options['groupby'] = isset($options['groupby']) && !empty($options['groupby']) ? $options['groupby'] : null;
        $options['page'] = isset($options['page']) ? $options['page'] : 1;
        $options['limit'] = isset($options['limit']) ? $options['limit'] : 0;
        $options['orm'] = isset($options['orm']) ? $options['orm'] : 1;
        $options['debug'] = isset($options['debug']) ? $options['debug'] : 0;

        $options['oneOrNull'] = isset($options['oneOrNull']) ? $options['oneOrNull'] == true : false;
        if ($options['oneOrNull']) {
            $options['limit'] = 1;
            $options['page'] = 1;
        }

        $options['count'] = isset($options['count']) ? $options['count'] == true : false;
        if ($options['count']) {
            $options['orm'] = false;
            $options['oneOrNull'] = true;
            $options['select'] = 'COUNT(*) AS count';
            $options['page'] = null;
            $options['limit'] = null;
        }

        $myClass = get_called_class();
        $tableName = static::getTableName();
        $fields = array_keys(static::getFields());

        $sql = "SELECT {$options['select']} FROM {$tableName} AS m";
        $sql .= $options['joins'] ? ' ' . $options['joins'] : '';
        $sql .= $options['whereSql'] ? ' WHERE ' . $options['whereSql'] : '';
        $sql .= $options['groupby'] ? ' GROUP BY ' . $options['groupby'] : '';
        if ($options['sort']) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        }
        if ($options['limit'] && $options['page']) {
            $sql .= " LIMIT " . (($options['page'] - 1) * $options['limit']) . ", " . $options['limit'];
        }

        if ($options['debug']) {
            while (@ob_end_clean()) ;
            var_dump($sql, $options['params']);
            exit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($options['params']);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($options['orm']) {
            $orms = array();
            foreach ($result as $itm) {
                $orm = new $myClass($pdo);
                foreach ($fields as $field) {
                    if (isset($itm[$field])) {
                        $method = 'set' . ucfirst($field);
                        $orm->$method($itm[$field]);
                    }
                }
                $orms[] = $orm;
            }
            $result = $orms;
        }

        if ($options['oneOrNull']) {
            $result = reset($result) ?: null;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $tableName = static::getTableName();

        $pdo = $this->zdb->getConnection();
        $sql = "DELETE FROM `{$tableName}` WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($this->getId()));
    }

    /**
     * @return mixed
     */
    public function save()
    {
        $tableName = static::getTableName();
        $fields = array_keys(static::getFields());

        if (method_exists($this, 'getTitle')) {
            $slugify = new Slugify(['trim' => false]);
            $this->setSlug($slugify->slugify($this->getTitle()));
        }
        $this->setModified(date('Y-m-d H:i:s'));

        $sql = '';
        $params = array();
        if (!$this->getId()) {
            $sql = "INSERT INTO `{$tableName}` ";
            $part1 = '(';
            $part2 = ' VALUES (';
            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }

                $part1 .= "`$field`, ";
                $part2 .= "?, ";
                $method = 'get' . ucfirst($field);
                $params[] = $this->$method();
            }
            $part1 = rtrim($part1, ', ') . ')';
            $part2 = rtrim($part2, ', ') . ')';
            $sql = $sql . $part1 . $part2;
//            var_dump('<pre>', $sql, $params, '</pre>');exit;
        } else {
            $sql = "UPDATE `{$tableName}` SET ";
            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }
                $sql .= "`$field` = ?, ";
                $method = 'get' . ucfirst($field);
                $params[] = $this->$method();
            }
            $sql = rtrim($sql, ', ') . ' WHERE id = ?';
            $params[] = $this->id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        if (!$this->getId()) {
            $this->setId($this->pdo->lastInsertId());
        }
        return $this->getId();
    }

    /**
     * @return \ReflectionClass
     */
    public static function getReflectionClass()
    {
        return new \ReflectionClass(get_called_class());
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        $rc = static::getReflectionClass();
        $slugify = new Slugify(['trim' => false]);
        return $slugify->slugify($rc->getShortName(), '_');
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param \PDO $pdo
     */
    public function setPdo(\PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

}