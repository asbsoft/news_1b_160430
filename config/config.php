<?php
/*
  Common module configs.
  Here are parameters for DEFAULT initialization (additional) module's members.
  These parameters may be redefine when this module will attach as submodule to application on another module,
  for example: ..., 'modules' => ...,
    'sitenews' => [
        'class' => 'asb\yii2\modules\news_1b_160430\Module',
        'layoutPath' => '.../views/layouts',
        'routesConfig' => [
            'admin' => 'backend/news',
            'main' => 'about/news',
            'rest' => [
                 'urlPrefix'  => 'newsapi',
                 'sublink' => 'rest-news',
            ],
        ],
    ],
*/

use asb\yii2\common_2_170212\base\UniApplication;
use asb\yii2\common_2_170212\web\UserIdentity;
use asb\yii2\common_2_170212\i18n\LangHelper;
use asb\yii2\common_2_170212\helpers\EditorContentHelper;

use yii\rest\UrlRule as RestUrlRule;

$adminUrlPrefix = empty(Yii::$app->params['adminPath']) ? '' : Yii::$app->params['adminPath'] . '/';//var_dump($adminUrlPrefix);

$type = empty(Yii::$app->type) ? false : Yii::$app->type;//var_dump($type);exit;

return [
    //'params' => include(__DIR__ . '/params.php'),
/*
    //'layoutPath' => '@asb/yii2cms/modules/sys/views/layouts',
    'layouts' => [ // (module/application) type => basename
        'frontend' => 'layout_main',
        'backend'  => 'layout_admin',
    ],
*/
    'routesConfig' => [ // default: type => prefix|[config]
        'main'  => $type == UniApplication::APP_TYPE_BACKEND  ? false : [
            'urlPrefix' => 'news',
            'startLinkLabel' => 'News', // use default link ''
        ],
        'admin' => $type == UniApplication::APP_TYPE_FRONTEND ? false : [
            'urlPrefix' => $adminUrlPrefix . 'news',
            'startLink' => [
                'label' => 'News manager', //!! no translate here, it will translate using 'MODULE_UID/module' tr-category
              //'link'  => '', // default
                'action' => 'admin/index',
            ],
        ],
        'rest'  => [
             'class' => RestUrlRule::className(),
             'urlPrefix'  => 'rest-api',
             'sublink' => 'newsrest',
        ],
    ],

    /** shared models */
    'models' => [ // alias => class name or object array
        'News'            => 'asb\yii2\modules\news_1b_160430\models\News',
        'NewsI18n'        => 'asb\yii2\modules\news_1b_160430\models\NewsI18n',
        'NewsSearchFront' => 'asb\yii2\modules\news_1b_160430\models\NewsSearchFront',
        'NewsQuery'       => 'asb\yii2\modules\news_1b_160430\models\NewsQuery',
    ],

    'assets' => [ // alias => class name
        'AdminAsset' => 'asb\yii2\modules\news_1b_160430\assets\AdminAsset',
        'FrontAsset' => 'asb\yii2\modules\news_1b_160430\assets\FrontAsset',
    ],

    // external using classes
  //'userIdentity'  => UserIdentity::className(),
    'userIdentity'  => Yii::$app->user->identityClass,
    'langHelper'    => LangHelper::className(),
    'contentHelper' => EditorContentHelper::className(),

];
