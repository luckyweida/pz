<?php

namespace Pz\Form\Handler;

use Cocur\Slugify\Slugify;
use Pz\Axiom\Eve;
use Pz\Axiom\Walle;
use Pz\Orm\_Model;
use Pz\Orm\DataGroup;
use Pz\Redirect\RedirectException;
use Pz\Service\Db;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ModelHandler
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sync(_Model $model)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        /** @var Walle $fullClassName */
        $fullClassName = Db::fullClassName($model->getClassName());
        $fullClassName::sync($pdo);
    }

    public function handle(_Model $model)
    {
        if (!$model->getId()) {
            $request = Request::createFromGlobals();
            $requestUri = rtrim($request->getPathInfo(), '/');
            $fragments = explode('/', $requestUri);
            if (count($fragments) >= 5 && $fragments[4] == 'built-in') {
                $model->setModelType(1);
                $model->setDataType(1);
            } else {
                $model->setModelType(0);
                $model->setDataType(0);
            }
        }

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $dataGroups = array();
        /** @var DataGroup[] $result */
        $result = DataGroup::active($pdo);
        foreach ($result as $itm) {
            $dataGroups[$itm->getTitle()] = $itm->getId();
        }

        $columns = array_keys(_Model::getFields());
        $form = $this->container->get('form.factory')->create(\Pz\Form\Builder\Model::class, $model, array(
            'defaultSortByOptions' => array_combine($columns, $columns),
            'dataGroups' => $dataGroups,
        ));

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($model->getModelType() == 0) {
                $model->setNamespace('Web\\Orm');
            } else {
                $model->setNamespace('Pz\\Orm');
            }
            $this->setGenereatedFile($model);
            $this->setCustomFile($model);
            $model->save();

            $baseUrl = "/pz/admin/models/" . ($model->getModelType() == 0 ? 'customised' : 'built-in');
            $redirectUrl = "$baseUrl/sync/{$model->getId()}?returnUrl=";
            if ($request->get('submit') == 'Apply') {
                throw new RedirectException($redirectUrl . urlencode($request->getPathInfo()), 301);
            } else if ($request->get('submit') == 'Save') {
                throw new RedirectException($redirectUrl . urlencode($baseUrl), 301);
            }
        }

        return $form->createView();
    }

    private function setGenereatedFile(_Model $model)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $myClass = get_class($model);
        $fieldChoices = $myClass::getFieldChoices();
        $columnsJson = json_decode($model->getColumnsJson());
        $fields = array_map(function ($value) use ($fieldChoices) {
            $fieldChoice = $fieldChoices[$value->column];
            return <<<EOD
    /**
     * #pz {$fieldChoice}
     */
    private \${$value->field};
    
EOD;
        }, $columnsJson);

        $methods = array_map(function ($value) {
            $ucfirst = ucfirst($value->field);
            return <<<EOD
    /**
     * @return mixed
     */
    public function get{$ucfirst}()
    {
        return \$this->{$value->field};
    }
    
    /**
     * @param mixed {$value->field}
     */
    public function set{$ucfirst}(\${$value->field})
    {
        \$this->{$value->field} = \${$value->field};
    }
    
EOD;
        }, $columnsJson);

        $str = file_get_contents($this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/files/orm_generated.txt');
        $str = str_replace('{time}', date('Y-m-d H:i:s'), $str);
        $str = str_replace('{namespace}', $model->getNamespace() . '\\Generated', $str);
        $str = str_replace('{classname}', $model->getClassName(), $str);
        $str = str_replace('{fields}', join("\n", $fields), $str);
        $str = str_replace('{methods}', join("\n", $methods), $str);

        $path = $this->container->getParameter('kernel.project_dir') . ($model->getModelType() == 0 ? '' : '/vendor/pozoltd/pz') . '/src/' . str_replace('\\', '/', $model->getNamespace()) . '/Generated/';

        $file = $path . 'ModelJson/' . $model->getClassName() . '.json';
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, _Model::encodedModel($model));

        $file = $path . $model->getClassName() . '.php';
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, $str);

        if ($model->getId()) {
            $db = new Db($connection);
            /** @var _Model $model */
            $model = $db->getById('_Model', $model->getId());
            if ($model) {

                $slugify = new Slugify(['trim' => false]);
                $eve = new Eve($pdo, $slugify->slugify($model->getClassName()));
                if ($model->getClassName() != $model->getClassName()) {
                    $eve->rename($model->getClassName());
                }

                if ($eve->exists()) {
                    $tableFields = array_keys($eve->getFields());
                    $oldColumnJson = json_decode($model->getColumnsJson());

                    foreach ($oldColumnJson as $oldColumn) {
                        foreach ($columnsJson as $column) {
                            if ($oldColumn->column == $column->column && $oldColumn->field != $column->field && in_array($oldColumn->field, $tableFields)) {
                                $fieldChoice = $fieldChoices[$oldColumn->column];
                                $eve->renameColumn($oldColumn->field, $column->field, $fieldChoice);
                            }
                        }
                    }
                }
            }
        }

    }

    private function setCustomFile(_Model $model)
    {
        $path = $this->container->getParameter('kernel.project_dir') . ($model->getModelType() == 0 ? '' : '/vendor/pozoltd/pz') . '/src/' . str_replace('\\', '/', $model->getNamespace()) . '/';
        $file = $path . $model->getClassName() . '.php';
        if (!file_exists($file)) {
            $str = file_get_contents($this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/files/orm_custom.txt');
            $str = str_replace('{time}', date('Y-m-d H:i:s'), $str);
            $str = str_replace('{namespace}', $model->getNamespace(), $str);
            $str = str_replace('{classname}', $model->getClassName(), $str);

            $dir = dirname($file);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($file, $str);
        }
    }


}