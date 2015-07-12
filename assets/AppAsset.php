<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
	public $sourcePath='@app/themes/pk'; // папка, где хранятся все css и js
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $css = [
        //'css/site.css',
	    'bootstrap/css/bootstrap.css',
	    'fonts/font-awesome/css/font-awesome.css',
	    'css/animations.css',
	    'css/style.css',
	    'css/custom.css',
    ];
    public $js = [
	    'plugins/jquery.min.js',
	    'bootstrap/js/bootstrap.min.js',
	    'plugins/modernizr.js',
	    'plugins/isotope/isotope.pkgd.min.js',
	    'plugins/jquery.backstretch.min.js',
	    'js/template.js',
	    'js/custom.js',
    ];
    public $depends = [
       // 'yii\web\YiiAsset',
       // 'yii\bootstrap\BootstrapAsset',
    ];
}
