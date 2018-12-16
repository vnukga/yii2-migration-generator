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

class MigrateController extends Controller
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


}