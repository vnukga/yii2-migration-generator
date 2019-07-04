<?php
/**
 * Created by PhpStorm.
 * User: ГерманАл
 * Date: 09.12.2018
 * Time: 0:10
 */

namespace vnukga\migrationGenerator;

use vnukga\migrationGenerator\helpers\GiiHelper;
use yii\console\Controller;
use yii\helpers\Inflector;
use yii\web\Response;
use yii\helpers\VarDumper;
use vnukga\migrationGenerator\helpers\MigrationHelper;
use Yii;

class MigrateController extends Controller
{
    public $sourceClass;

    public $sourceId = 'vnukga\migrationGenerator\sources\GoogleSheet';

    public $useGii = false;

    public $acceptMigrations = true;

    public $modelNamespace = 'app\models';

    public $enableI18N = 1;

    public $modelBaseClass = 'yii\db\ActiveRecord';

    public $messageCategory = 'app';

    public $controllerNamespace = 'admin\controllers';

    public $baseControllerClass = 'yii\web\Controller';

    public $baseViewPath = 'admin\views';

    public $keyPath = '';



    public function actionIndex(){
        $sourceClass = $this->sourceClass;
        $source = new $sourceClass();
        $source->keyPath = $this->keyPath;
        $currentSchema = $this->getCurrentSchema();
        $newSchema = $source->getPreparedArray($this->sourceId);
        $diffs = $this->getDiffArray($currentSchema, $newSchema);
        $helper = new MigrationHelper();
        $helper->createMigrationsFromArray($diffs);
        if($this->acceptMigrations) {
            $helper->acceptMigrations();
        }
        if($this->useGii){
            $this->useGiiHelper($diffs);
        }
    }

    private function getCurrentSchema(){
        $currentTableShemas = Yii::$app->db->schema->tableSchemas;
        $schemaAsArray = [];
        foreach ($currentTableShemas as $schema) {
            $name = $schema->name;
            if ($name == 'migration' || $name == 'user'){
                continue;
            }
            foreach ($schema->columns as $column => $columnSchema){
//                if($column == 'id') continue;
                $columnSchema->allowNull ? $required = '' : $required = true;
                $schemaAsArray[$name][$column] = [
                    'type' =>$columnSchema->type,
                    'required' =>$required,
                    'comment' =>$columnSchema->comment,
                    'defaultValue' =>$columnSchema->defaultValue,
                ];
                foreach ($schema->foreignKeys as $foreignKey => $attributes) {
                    if(strpos($foreignKey, $column)){
                        $schemaAsArray[$name][$column]['foreignKey'] = $attributes[0];
                    }
                }
            }

        }
        return $schemaAsArray;
    }

    private function getDiffArray($currentSchema,$newSchema){
        $difference = [];
        $this->getDiffs($newSchema,$currentSchema,'new_columns','new_tables',$difference);
        $this->getDiffs($currentSchema,$newSchema,'old_columns','old_tables',$difference);
        krsort($difference);
        return $difference;
    }

    private function getDiffs($array1,$array2,$columnField,$tableField,&$difference){
        foreach($array1 as $tableName => $columns)
        {
            if($array2[$tableName]) {
                foreach ($columns as $columnName => $column) {
                    if($array2[$tableName][$columnName]){
//                        TODO сделать проверку параметров столбца
                    } else {
                        $difference[$columnField][$tableName][$columnName] = $column;
                    }
                }
            } else {
                $difference[$tableField][$tableName] = $columns;
            }
        }
    }

    private function useGiiHelper(array $values)
    {
        $gii = new GiiHelper();
        $gii->modelNamespace = $this->modelNamespace;
        $gii->enableI18N = $this->enableI18N;
        $gii->modelBaseClass = $this->modelBaseClass;
        $gii->messageCategory = $this->messageCategory;
        $gii->controllerNamespace = $this->controllerNamespace;
        $gii->baseControllerClass = $this->baseControllerClass;
        $gii->baseViewPath = $this->baseViewPath;
        $gii->generate($values);
    }
}