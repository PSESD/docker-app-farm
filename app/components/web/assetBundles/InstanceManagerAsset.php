<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\web\assetBundles;

use yii\web\AssetBundle;

class InstanceManagerAsset extends AssetBundle
{
    public $sourcePath = '@canis/appFarm/assets/instance_manager';
    public $css = [
        'css/canis.instanceManager.css',
    ];
    public $js = [
        'js/canis.instanceManager.js',
    ];
    public $depends = [
        'canis\appFarm\components\web\assetBundles\AppAsset',
        'canis\web\assetBundles\CanisLogViewerAsset'
    ];
}
