<?php


namespace vnukga\migrationGenerator\helpers;


use yii\helpers\Inflector;

class GiiHelper extends BaseHelper
{
    public $modelNamespace;

    public $enableI18N;

    public $modelBaseClass;

    public $messageCategory;

    public $controllerNamespace;

    public $baseControllerClass;

    public $baseViewPath;

    public function generate(array $values)
    {
        foreach ($values['new_tables'] as $table_name => $table){
            $this->generateModel($table_name);
            $this->generateCrud($table_name);
        }
    }

    private function generateModel($table_name)
    {
        $modelClass = Inflector::id2camel($table_name, '_');
        $command = 'yii gii/model --tableName=' . $table_name
            . ' --modelClass=' . $modelClass .
            ' --ns=' . $this->modelNamespace .
            ' --enableI18N=' . $this->enableI18N .
            ' --messageCategory=' . $this->messageCategory .
            ' --baseClass=' . $this->modelBaseClass . ' interactive=0';
        $this->applyConsoleCommand($command);
    }

    private function generateCrud($table_name){
        $modelName = Inflector::id2camel($table_name, '_');
        $modelClass = $this->modelNamespace . '\\' . $modelName;
        $controllerName = $modelName . 'Controller';
        $command = 'yii gii/crud' .
            ' --modelClass=' . $modelClass .
            ' --searchModelClass=' . $modelClass . 'Seacrh' .
            ' --baseControllerClass=' . $this->baseControllerClass .
            ' --controllerClass=' . $this->controllerNamespace . '\\' . $controllerName .
            ' --viewPath=' . $this->baseViewPath . DIRECTORY_SEPARATOR . Inflector::camel2id($modelName) .
            ' --enableI18N=' . $this->enableI18N .
            ' --messageCategory=' . $this->messageCategory .
            ' interactive=0';
        $this->applyConsoleCommand($command);
    }
}