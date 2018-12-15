<?php
/**
 * Created by PhpStorm.
 * User: ГерманАл
 * Date: 09.12.2018
 * Time: 0:10
 */

namespace vnukga\migrationGenerator;

use yii\console\Controller;
use yii\web\Response;
use yii\helpers\VarDumper;
use Yii;

class JsonMigrateController extends Controller
{

    public $sourceClass;

    public $sourceId;

    public function actionIndex(){

        $sourceClass = $this->sourceClass;
        $source = new $sourceClass();
        $currentSchema = $this->getCurrentSchema();
        $newSchema = $source->getPreparedArray($this->sourceId);
        $diffs = $this->getDiffArray($currentSchema, $newSchema);
        $helper = new MigrationHelper();
        $helper->createMigrationsFromArray($diffs);
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
                if($column == 'id') continue;
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
        foreach($newSchema as $tableName => $columns)
        {
            if($currentSchema[$tableName]) {
                foreach ($columns as $columnName => $column) {
                    if($currentSchema[$tableName][$columnName]){
//                        TODO сделать проверку параметров столбца
                    } else {
                        $difference['new_columns'][$tableName][$columnName] = $column;
                    }
                }
            } else {
                $difference['new_tables'][$tableName] = $columns;
            }
        }
        krsort($difference);
        return $difference;
    }
}