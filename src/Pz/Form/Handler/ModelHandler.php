<?php

namespace Pz\Form\Handler;

use Cocur\Slugify\Slugify;
use Pz\Axiom\Eve;
use Pz\Axiom\Walle;
use Pz\Orm\_Model;
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

    public function sync($orm)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        /** @var Walle $className */
        $className = $orm->getNamespace() . '\\Generated\\' . $orm->getClassName();
        $className::sync($pdo);
    }

    public function handle(_Model $orm)
    {
        if (!$orm->getId()) {
            $request = Request::createFromGlobals();
            $requestUri = rtrim($request->getPathInfo(), '/');
            $fragments = explode('/', $requestUri);
            if (count($fragments) >= 3 && $fragments[2] == 'admin') {
                $orm->setModelType(1);
                $orm->setDataType(1);
            }
        }

        $myClass = get_class($orm);
        $columns = array_keys($myClass::getFields());
        $form = $this->container->get('form.factory')->create(\Pz\Form\Builder\Model::class, $orm, array(
            'defaultSortByOptions' => array_combine($columns, $columns),
        ));

        $request = Request::createFromGlobals();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($orm->getModelType() == 0) {
                $orm->setNamespace('Web\\Orm');
            } else {
                $orm->setNamespace('Pz\\Orm');
            }
            $this->setGenereatedFile($orm);
            $this->setCustomFile($orm);
            $orm->save();

            $baseUrl = "/pz/admin/models/" . ($orm->getModelType() == 0 ? 'customised' : 'built-in');
            $redirectUrl = "$baseUrl/sync/{$orm->getId()}?returnUrl=";
            if ($request->get('submit') == 'apply') {
                throw new RedirectException($redirectUrl . urlencode($request->getPathInfo()), 301);
            } else if ($request->get('submit') == 'save') {
                throw new RedirectException($redirectUrl . urlencode($baseUrl), 301);
            }
        }

        return $form->createView();
    }

    private function setGenereatedFile(_Model $orm)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $myClass = get_class($orm);
        $fieldChoices = $myClass::getFieldChoices();
        $columnsJson = json_decode($orm->getColumnsJson());
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
        $str = str_replace('{namespace}', $orm->getNamespace() . '\\Generated', $str);
        $str = str_replace('{classname}', $orm->getClassName(), $str);
        $str = str_replace('{fields}', join("\n", $fields), $str);
        $str = str_replace('{methods}', join("\n", $methods), $str);

        $path = $this->container->getParameter('kernel.project_dir') . ($orm->getModelType() == 0 ? '' : '/vendor/pozoltd/pz') . '/src/' . str_replace('\\', '/', $orm->getNamespace()) . '/Generated/';

        $file = $path . 'ModelJson/' . $orm->getClassName() . '.json';
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, _Model::encodedModel($orm));

        $file = $path . $orm->getClassName() . '.php';
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, $str);

        if ($orm->getId()) {
            $db = new Db($connection);
            /** @var _Model $model */
            $model = $db->getById('_Model', $orm->getId());
            if ($model) {

                $slugify = new Slugify(['trim' => false]);
                $eve = new Eve($pdo, $slugify->slugify($model->getClassName()));
                if ($model->getClassName() != $orm->getClassName()) {
                    $eve->rename($orm->getClassName());
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

    private function setCustomFile(_Model $orm)
    {
        $path = $this->container->getParameter('kernel.project_dir') . ($orm->getModelType() == 0 ? '' : '/vendor/pozoltd/pz') . '/src/' . str_replace('\\', '/', $orm->getNamespace()) . '/';
        $file = $path . $orm->getClassName() . '.php';
        if (!file_exists($file)) {
            $str = file_get_contents($this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/files/orm_custom.txt');
            $str = str_replace('{time}', date('Y-m-d H:i:s'), $str);
            $str = str_replace('{namespace}', $orm->getNamespace(), $str);
            $str = str_replace('{classname}', $orm->getClassName(), $str);

            $dir = dirname($file);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($file, $str);
        }
    }


}