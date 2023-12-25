<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'shop',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Murad',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                // При добавлении функции, добавляем сюда ее. [метод] [/api/...] => [названия contoller]/[название функции]
                'POST register' => 'user/create',                           // Регистрация (actionCreate файл UserControllers 13)
                'POST login' => 'user/login',                               // Авторизация (actionLogin файл UserControllers 53)
                'GET products' => 'products/products',                               // Получить список всех продуктов (actionProducts файл ProductsControllers 13)
                'GET product/<id_product:\d+>' => 'products/product',                  // Получить один продукт (actionProduct файл ProductsControllers 33)
                'POST orders/add/<id_product:\d+>' => 'orders/add',            // Добавление в корзину (actionAdd файл OrdersControllers 14)
                'DELETE orders/delete/<order_id:\d+>' => 'orders/delete',    // Удаление из корзины !!! (actionDelete файл OrdersControllers 71)
                'POST product/add' => 'products/add',                            // Добавление продукта в систему (actionAdd файл ProductsControllers 55)
                'DELETE product/delete/<id_product:\d+>' => 'products/delete',      // Удаление продукта из системы (actionDelete файл ProductsControllers 132)
                'POST product/update/<id_product:\d+>' => 'products/update',        // Изменение данных о продукте (actionUpdate файл ProductsControllers 171)
                'GET user/<id_user:\d+>' => 'user/user',                    // Получение данных пользователя (actionUser файл UserControllers 96)
                'GET users' => 'user/users',                                // Получения данных всех пользователей (actionUsers файл UserControllers 134)
                'GET user/orders' => 'user/orders',
                // 'GET user/orders/<id_user:\d+>' => 'user/orders',           // Просмотр корзины !!! (actionOrders файл UserControllers 167)
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;
