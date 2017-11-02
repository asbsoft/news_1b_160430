<?php

namespace asb\yii2\modules\news_1b_160430\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class FrontAsset extends AssetBundle
{
    public $css = [
        'news-front.css',
    ];

    public $js = [
        'news.js',
    ];
    public $jsOptions = ['position' => View::POS_BEGIN];

    public $depends = [
        'asb\yii2\common_2_170212\assets\BootstrapCssAsset', // add only CSS - need to move up 'bootstrap.css' in <head>s of render HTML-results
    ];

    public function init() {
        parent::init();
        $this->sourcePath = __DIR__ . '/front';
    }
}
