<?php
/**
 * Created by PhpStorm.
 * User: ГерманАл
 * Date: 15.12.2018
 * Time: 23:05
 */

namespace vnukga\migrationGenerator;

/**
 * Class MigrationHelper
 * @package console\modules\jsonMigrations
 */

class MigrationHelper
{
    public function createMigrationsFromArray($diffs){
        foreach ($diffs as $type => $diff){
            switch ($type){
                case 'new_tables': $this->createNewTablesMigrations($diff);
                    break;
                case 'new_columns': $this->createNewColumnsMigratios($diff);
                    break;
            }
        }
    }

    private function createNewColumnsMigratios($tables){
        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $columnName => $params) {
                $fields = '--fields='.$columnName.$this->getFieldParamsAsString($params);
                $command = 'Yii migrate/create add_'.$columnName.'_column_to_'.$tableName.'_table '.$fields;
                $consoleHandle = popen($command,'w');
                fwrite($consoleHandle,'Y');
                pclose($consoleHandle);
                sleep(1);

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
            $consoleHandle = popen($command,'w');
            fwrite($consoleHandle,'Y');
            pclose($consoleHandle);
            sleep(1);
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
//                            case 'comment': $fields .= ":comment('$param')"; TODO разобраться с комментариями
//                                break;
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