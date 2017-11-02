<?php

use asb\yii2\modules\news_1b_160430\models\News;
use asb\yii2\modules\news_1b_160430\models\NewsI18n;

$filesSubpath = 'news';
//$uploadsRoot = 'uploads/news';
//var_dump(Yii::$aliases);exit;

return [
    'label'   => 'News manager',
    'version' => '1b.160430', // using asb\yii2\common_2_170212
    'origin'  => 'news_1b_160430 @ common_2_170212',

    'filesSubpath'        => $filesSubpath,
  //'uploadsNewsDir'      => "@webroot/{$uploadsRoot}", //!! deprecated
  //'uploadsNewsUrl'      => "@web/{$uploadsRoot}", //!! deprecated
    'uploadsNewsDir'      => "@uploadspath/{$filesSubpath}",
    'uploadsNewsUrl'      => "@webfilesurl/{$filesSubpath}",
    'uploadsCommonSubdir' => 'common',

    'maxImageSize' => 102400, //bytes

    //lists oage size
    'pageSizeAdmin' => 8,
    'pageSizeFront' => 5,
    'pageSizeRest'  => 20,

    'allMultilangFieldsRequired' => false, // FALSE - need news for at least one language
  //'allMultilangFieldsRequired' => true,

    // indicate when article published (is_visible = true) author can't edit it
    'canAuthorEditOwnVisibleArticle' => false,
  //'canAuthorEditOwnVisibleArticle' => true,

    // set TRUE to show in edit form all registered languages, not only visible
    'editAllLanguages' => false,
  //'editAllLanguages' => true,

    'showAdminSqlErrors' => true,
  //'showAdminSqlErrors' => false,

    'titleMinLength' => 5, //symbols
    'bodyMinLength' => 50, //symbols - minimal length of news

    'restLangAreaTag' => '_multilang_',

    News::className() => [
        'tableName' => '{{%news}}',
    ],
    NewsI18n::className() => [
        'tableName' => '{{%news_i18n}}',
    ],

];
