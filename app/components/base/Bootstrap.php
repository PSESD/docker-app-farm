<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\base;

use yii\base\BootstrapInterface;

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
    }
}
