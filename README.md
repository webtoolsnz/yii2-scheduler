# yii2-scheduler

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webtoolsnz/yii2-scheduler/badges/quality-score.png?b=master&s=8e54a25c083835ea391bee61d40ce0ec3c5b75cf)](https://scrutinizer-ci.com/g/webtoolsnz/yii2-scheduler/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/webtoolsnz/yii2-scheduler/badges/build.png?b=master&s=3df6c1a7e97264cb03f8e25a41931a72ed6de975)](https://scrutinizer-ci.com/g/webtoolsnz/yii2-scheduler/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/webtoolsnz/yii2-scheduler/badges/coverage.png?b=master&s=96b0b17cb100fa786f29f51c752b7d07b9503127)](https://scrutinizer-ci.com/g/webtoolsnz/yii2-scheduler/?branch=master)


A scheduled task manager for yii2

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Install using the following command.

~~~bash
$ composer require webtoolsnz/yii2-scheduler
~~~

Now that the  package has been installed you need to configure the module in your application

The `bootstrap` and `modules` sections in the `config/console.php` and `config/web.php` files will need to be updated to include the `scheduler` module.
~~~php
    'bootstrap' => ['log', 'scheduler'],
    'modules' => [
            'scheduler' => ['class' => 'webtoolsnz\scheduler\Module'],
        ],
~~~

After the configuration files have been updated, a `tasks` directory will need to be created in the root of your project.


Run the database migrations, which will create the necessary tables for `scheduler`
~~~bash
php yii migrate up --migrationPath=vendor/webtoolsnz/yii2-scheduler/src/migrations
~~~

Add a controller
~~~php
<?php

namespace app\modules\admin\controllers;

use yii\web\Controller;

/**
 * Class SchedulerController
 * @package app\modules\admin\controllers
 */
class SchedulerController extends Controller
{
    public function actions()
    {
        return [
            'index' => [
                'class' => 'webtoolsnz\scheduler\actions\IndexAction',
                'view' => '@scheduler/views/index',
            ],
            'update' => [
                'class' => 'webtoolsnz\scheduler\actions\UpdateAction',
                'view' => '@scheduler/views/update',
            ],
            'view-log' => [
                'class' => 'webtoolsnz\scheduler\actions\ViewLogAction',
                'view' => '@scheduler/views/view-log',
            ],
        ];
    }
}
~~~

## Example Task

You can now create your first task using scheduler, create the file `AlphabetTask.php` inside the `tasks` directory in your project root.

Paste the below code into your task:
~~~php
<?php
namespace app\tasks;

/**
 * Class AlphabetTask
 * @package app\tasks
 */
class AlphabetTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Prints the alphabet';
    public $schedule = '0 * * * *';
    public function run()
    {
        foreach (range('A', 'Z') as $letter) {
            echo $letter;
        }
    }
}
~~~

The above code defines a simple task that runs at the start of every hour, and prints the alphabet.

The `$schedule` property of this class defines how often the task will run, these are simply [Cron Expression](http://en.wikipedia.org/wiki/Cron#Examples)



## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.