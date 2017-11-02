<?php

namespace asb\yii2\modules\news_1b_160430\controllers;

use asb\yii2\modules\news_1b_160430\Module;

use asb\yii2\common_2_170212\widgets\ckeditor\ElFinderController as BaseController;

//use mihaildev\elfinder\Controller as BaseController; // use for additional common files root
//use mihaildev\elfinder\PathController as BaseController; // select for use only one (separate) files root for news
use mihaildev\elfinder\PathController;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class ElFinderController extends BaseController
{
    /** Default role(s) for all actions. Use instead of behaviors()['access'] */
    public $access = ['roleNewsAuthor', 'roleNewsModerator'];

    /** Display files mime types */
    //public $onlyMimes = ['image']; // default in elFinder
    //public $onlyMimes = ['all']; // show all

    /** Allow to upload files mime types */
    //public $uploadAllow = ['image']; // default in parent

    /**
     * @inheritdoc
     * Need to add news id to connector's URL. Every news will have it's own uploads dir.
     */
    public function getManagerOptions()
    {
        $options = parent::getManagerOptions();
        $id = Yii::$app->request->getQueryParam('id', 0);
        $options['url'] = Url::toRoute(['connect', 'id' => $id]);
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $module = Module::getModuleByClassname(Module::className());
        $this->tc = $module->tcModule;

        $uploadsNewsUrl = Yii::getAlias($module->params['uploadsNewsUrl']);//var_dump($uploadsNewsUrl);
        $uploadsNewsDir = Yii::getAlias($module->params['uploadsNewsDir']);//var_dump($uploadsNewsDir);exit;

        // common images root(s) for mihaildev\elfinder\Controller
        $roots = [
            [
                'name' => Yii::t($this->tc, 'All news common image(s)'),
                'baseUrl'  => $uploadsNewsUrl,
                'basePath' => $uploadsNewsDir,
                'path'     => $module->params['uploadsCommonSubdir'],
                'options'  => $this->_addOptions,
            ],
        ];

        $id = Yii::$app->request->getQueryParam('id', 0);//var_dump($id);
        $newsModel = $module::model('News');
        $subdir = $newsModel::getImageSubdir($id);

        $dir = Yii::getAlias($uploadsNewsDir . '/' . $subdir);//var_dump($dir);
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        $root = [
            'name' => Yii::t($this->tc, 'This news image(s)'),
            'basePath' => $uploadsNewsDir,
            'baseUrl'  => $uploadsNewsUrl,
            'path'     => $subdir,
            'options'  => $this->_addOptions,
        ];

        if ($this instanceof PathController) {
            $this->root = $root; // only one files root here
        } else { // elFinder-manager has many files roots by default
            $this->roots = $roots;
            array_unshift($this->roots, $root);//var_dump($this->roots);exit;
        }
    }

}
