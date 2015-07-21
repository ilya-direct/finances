<?php

namespace app\assets;

use yii\web\AssetBundle;


class WorthyAsset extends AssetBundle
{
	public $sourcePath='@app/themes/worthy'; // папка, где хранятся все css и js
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
		'plugins/jquery.appear.js',
		'js/template.js',
		'js/custom.js',
	];
	public $depends = [
		// 'yii\web\YiiAsset',
		// 'yii\bootstrap\BootstrapAsset',
	];
}
