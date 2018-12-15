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

Once the extension is installed, simply use it in your code by  :

```php
<?= \vnukga\migrationGenerator\AutoloadExample::widget(); ?>```