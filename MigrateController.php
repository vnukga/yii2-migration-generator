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
    /**
     * Contains name of a class, that is used to getting data with database-schema.
     * Default value uses Google Sheet's API.
     * @var string $sourceClass
     */
    public $sourceClass = 'vnukga\migrationGenerator\sources\GoogleSheet';

    /**
     * Id of data-source. If using default source class, there should be an id of Google Sheet
     * @var string $sourceId
     */
    public $sourceId;

    /**
     * If true, migrations will be applied after creation and console controller 'gii' will be used to generate models
     * and CRUD.
     * @var bool $useGii
     */
    public $useGii = false;

    /**
     * If true, migrations will be applied after creation.
     * @var bool $acceptMigrations
     */
    public $acceptMigrations = true;

    /**
     * Namespace for models, that will be created by using Gii (if property $useGii is set to true).
     * @var string $modelNamespace
     */
    public $modelNamespace = 'app\models';

    /**
     * If 1, Yii::t() will be used for generated model's labels.
     * @var int $enableI18N
     */
    public $enableI18N = 1;

    /**
     * Base Model's class for models, generated by Gii.
     * @var string $modelBaseClass
     */
    public $modelBaseClass = 'yii\db\ActiveRecord';

    /**
     * Category for Yii::t() (if $enableI18N is set to 1)
     * @var string $messageCategory
     */
    public $messageCategory = 'app';

    /**
     * Namespace for CRUD controllers, generated by Gii.
     * @var string $controllerNamespace
     */
    public $controllerNamespace = 'admin\controllers';

    /**
     * Base Controller's class for controllers, generated by Gii.
     * @var string $baseControllerClass
     */
    public $baseControllerClass = 'yii\web\Controller';

    /**
     * Path for generating view's files via Gii (if property $useGii is set to true).
     * @var string $baseViewPath
     */
    public $baseViewPath = 'admin\views';

    /**
     * Path for Google's credentials file.
     * @var string $keyPath
     */
    public $keyPath = '';

    /**
     * Base action. Gets database schema using $sourceClass.
     * Then compares it with current schema and generates migrations based on diff-array.
     * If $useGii is set to "true", also generates models and CRUD for new tables.
     */
    public function actionIndex()
    {
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

    /**
     * Returns array, contains current db-schema.
     * @return array
     */
    private function getCurrentSchema()
    {
        $currentTableShemas = Yii::$app->db->schema->tableSchemas;
        $schemaAsArray = [];
        foreach ($currentTableShemas as $schema) {
            $name = $schema->name;
            if ($name == 'migration' || $name == 'user'){
                continue;
            }
            foreach ($schema->columns as $column => $columnSchema){
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

    /**
     * Takes current and new db-schemas. Returns differences.
     * @param array $currentSchema
     * @param array $newSchema
     * @return array
     */
    private function getDiffArray(array $currentSchema, array $newSchema)
    {
        $difference = [];
        $this->getDiffs($newSchema,$currentSchema,'new_columns','new_tables',$difference);
        $this->getDiffs($currentSchema,$newSchema,'old_columns','old_tables',$difference);
        krsort($difference);
        return $difference;
    }

    /**
     * Writes difference to array.
     * @param array $array1
     * @param array $array2
     * @param string $columnField
     * @param string $tableField
     * @param array $difference
     */
    private function getDiffs(array $array1, array $array2, string $columnField, string $tableField, array &$difference)
    {
        foreach($array1 as $tableName => $columns)
        {
            if($array2[$tableName]) {
                foreach ($columns as $columnName => $column) {
                    if($array2[$tableName][$columnName]){
                    } else {
                        $difference[$columnField][$tableName][$columnName] = $column;
                    }
                }
            } else {
                $difference[$tableField][$tableName] = $columns;
            }
        }
    }

    /**
     * Initiate GiiHelper if $useGii is set to 'true'
     * @param array $values
     */
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