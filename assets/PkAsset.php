<?php

namespace app\assets;

use yii\web\AssetBundle;


class PkAsset extends AssetBundle
{
	public $sourcePath='@app/themes/pk'; // папка, где хранятся все css и js
	public $css=[
		'css/viewer.css',
	];
	public $js=[
		'js/require.js',
		'js/main-r.js',
	];
	/*
	public $css = [
		'css/cyrillic.css',
		'css/googlefont.css',
		'css/latin.css',
		'css/mobileIEFIX.css',
		'css/viewer.css',
	];
	public $js = [
		'js/dc.js',
		'js/require.js',
		'js/main-r.js',
		'js/skins.js',
		'js/components.js',
		'js/core.js',
		'js/utils.js',
		'js/TweenMax.js',
		'js/wixappsCore.js',
		'js/wixappsClassics.js',
		'js/layout.js',
		'js/tpa.js',
		'js/ScrollToPlugin.js',
		'js/wixappsBuilder.js',
		'js/fonts.js',
		'js/animations.js',
		'js/swfobject.js',
		'js/mousetrap.js',
		'js/experiments.js',
		'js/tweenEngine.js',
		'js/react-with-addons.js',
		'js/lodash.js',
		'js/zepto.js',
		'js/color.js',
		'js/DrawSVGPlugin.js',
	];
	*/
	public $depends = [
		// 'yii\web\YiiAsset',
		// 'yii\bootstrap\BootstrapAsset',
	];
}
