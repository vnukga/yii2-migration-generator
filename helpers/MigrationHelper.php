<?php
/**
 * Created by PhpStorm.
 * User: ГерманАл
 * Date: 15.12.2018
 * Time: 23:05
 */

namespace vnukga\migrationGenerator\helpers;

/**
 * Class MigrationHelper
 * @package vnukga\jsonMigrations
 */

class MigrationHelper extends BaseHelper
{
    public function createMigrationsFromArray($diffs){
        foreach ($diffs as $type => $diff){
            switch ($type){
                case 'new_tables': $this->createNewTablesMigrations($diff);
                    break;
                case 'new_columns': $this->createNewColumnsMigratios($diff);
                    break;
                case 'old_tables': $this->createDropTablesMigrations($diff);
                    break;
                case 'old_columns': $this->createDropColumnsMigratios($diff);
                    break;

            }
        }
    }

    public function acceptMigrations()
    {
        $command = 'yii migrate';
        $this->applyConsoleCommand($command);
    }

    private function createNewColumnsMigratios($tables){
        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $columnName => $params) {
                $fields = '--fields='.$columnName.$this->getFieldParamsAsString($params);
                $command = 'Yii migrate/create add_'.$columnName.'_column_to_'.$tableName.'_table '.$fields;
                $this->applyConsoleCommand($command);
            }
        }
    }

    private function createNewTablesMigrations($tables){
        foreach ($tables as $key => $item)
        {
            $tableName = $key;
            $fieldsArray = $item;
            $fields = $this->getFieldsAsString($fieldsArray);
            $command = 'Yii migrate/create create_'.$tableName.'_table '.$fields;
            $this->applyConsoleCommand($command);
        }
    }

    private function createDropColumnsMigratios($tables){
        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $columnName => $params) {
                $fields = '--fields='.$columnName.$this->getFieldParamsAsString($params);
                $command = 'Yii migrate/create drop_'.$columnName.'_column_from_'.$tableName.'_table '.$fields;
                $this->applyConsoleCommand($command);
            }
        }
    }

    private function createDropTablesMigrations($tables){
        foreach ($tables as $key => $item)
        {
            $tableName = $key;
            $fieldsArray = $item;
            $fields = $this->getFieldsAsString($fieldsArray);
            $command = 'Yii migrate/create drop_'.$tableName.'_table '.$fields;
            $this->applyConsoleCommand($command);
        }
    }

    private function getFieldsAsString($fieldsArray)
    {
        $fields = '--fields=';
        foreach ($fieldsArray as $fieldName => $params)
        {
            if($fieldName == 'id')
            {
                continue;
            }
            $fields .= $fieldName;
            $fields.= $this->getFieldParamsAsString($params);
            $fields .= ',';
        }
        $fields = substr($fields,0,-1);
        return $fields;
    }

    private function getFieldParamsAsString($params){
        $paramsString = '';
        foreach ($params as $paramName => $param)

        {
            if($param != '')
            {
                switch ($paramName)
                {
                    case 'type': $paramsString .= ':'.$param;
                        break;
                    case 'required': $paramsString .= ':notNull';
                        break;
                    case 'defaultValue': $paramsString .= ":defaultValue('$param')";
                        break;
                    case 'foreignKey': $paramsString .= ':foreignKey('.$param.')';
                        break;
                }
            }
        }
        return $paramsString;
    }
}