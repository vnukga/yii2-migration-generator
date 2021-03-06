Yii2-based generator for creating migrations from external sources
==================================================================
Allows to generate migrations from scheme inexternal sources, such as Google Spreadsheets, using console.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist vnukga/yii2-migration-generator "*"
```

or add

```
"vnukga/yii2-migration-generator": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, add the following code to "controllerMap" section in 'console/config/main.php'  :

```
'migrate-generator' => [
            'class' => 'vnukga\migrationGenerator\MigrateController',
            'sourceClass' => 'vnukga\migrationGenerator\sources\google\GoogleApiSheet',
            'sourceId' => 'Your Google-Sheet ID',
            'useGii' => true, //this allows you to generate models and GRUD's automatically
            'modelNamespace' => 'common\models',
            'keyPath' => 'Your path to a folder with credentials file' //__DIR__ . '/../../google'
        ]
```
