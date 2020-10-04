# error-code
php 错误码 对象化

依赖
```php
marc-mabe/php-enum
roave/better-reflection
``` 

文档
--------
基础用法[marc-mabe/php-enum](https://github.com/marc-mabe/php-enum)

增加功能
--------
- 基于注解 获得备注信息
- 合并多个错误码文件为一个文件脚本

案例 一
--------
```php
<?php

declare(strict_types=1);

namespace App\Constants\Error;

use Lengbin\ErrorCode\BaseEnum;

class ErrorCode extends BaseEnum
{
    /**
     * @Message("成功")
     */
    const SUCCESS = '0';
}

$error = ErrorCode::byValue(ErrorCode::SUCCESS);

$error->getName();  // SUCCESS
$error->getMessage(); // 成功
$error->getValue(); // 0


```

案例 二
--------
在整个项目开发中，错误字典会定义很多，如果都放在同一个文件中
- 不利于阅读，不清晰明了
- 自定错误码容易混淆
- 自定错误码分层蛋疼

```php
    
    $mergeErrorCode = new \Lengbin\ErrorCode\Command\Merge([
        'path'           => [
            BASE_PATH . '/vendor/lengbin/hyperf-common/src/Error',
            BASE_PATH . '/app/Constants/Errors'
        ],
        'classname'      => 'Error',
        'classNamespace' => 'App\\Constants',
        'output'         => BASE_PATH . '/app/Constants',
    ]);
    $mergeErrorCode->generate();
```
将在项目目录下生成 App/Constants/Error.php 文件


Install
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require lengbin/error-code
```

or add

```
"lengbin/error-code": "*"
```
to the require section of your `composer.json` file.

