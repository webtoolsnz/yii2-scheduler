<?php

$config = [
    'id'        => 'yii2-user-test',
    'basePath'  => dirname(__DIR__),
    'bootstrap' => ['scheduler'],
    'extensions' => require(VENDOR_DIR.'/yiisoft/extensions.php'),
    'aliases' => [
        '@vendor'        => VENDOR_DIR,
        '@bower'         => VENDOR_DIR.'/bower',
        '@tests' => dirname(__DIR__).'/../',
        '@tests/config' => '@tests/_config',
    ],
    'modules' => [
        'scheduler' => [
            'class' => 'webtoolsnz\scheduler\Module',
        ],
    ],
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__.'/../assets',
        ],
        'log'   => null,
        'cache' => null,
        'request' => [
            'enableCsrfValidation'   => false,
            'enableCookieValidation' => false,
        ],
        'db' => require __DIR__.'/db.php',
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
    ],
];

if (defined('YII_APP_BASE_PATH')) {
    $config = Codeception\Configuration::mergeConfigs(
        $config,
        require YII_APP_BASE_PATH.'/tests/codeception/config/config.php'
    );
}

return $config;
