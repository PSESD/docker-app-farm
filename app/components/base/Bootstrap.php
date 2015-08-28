<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\base;

use yii\base\BootstrapInterface;
use canis\base\Cron;
use canis\base\Daemon;
use yii\base\Event;
use canis\appFarm\components\engine\Engine;

/**
 * Bootstrap Run on each request.
 */
class Bootstrap extends \yii\base\Object implements BootstrapInterface
{
    /**
     * @inheritdocs.
     */
    public function bootstrap($app)
    {
    	Event::on(Daemon::className(), Daemon::EVENT_TICK, [Engine::className(), 'checkUninitialized']);
        Event::on(Daemon::className(), Daemon::EVENT_POST_TICK, [Engine::className(), 'failUninitialized']);
    }
}
