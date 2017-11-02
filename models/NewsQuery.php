<?php

namespace asb\yii2\modules\news_1b_160430\models;

use asb\yii2\modules\news_1b_160430\Module;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[News]].
 *
 * @see News
 */
class NewsQuery extends ActiveQuery
{
    public $languages;
    public $langCodeMain;

    public function init()
    {
        parent::init();

        $module = Module::getModuleByClassname(Module::className());
        $langHelper = $module->langHelper;
        $this->languages = $langHelper::activeLanguages();

        if (empty($this->langCodeMain) ) $this->langCodeMain = $langHelper::normalizeLangCode(Yii::$app->language);
    }

/*
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }
*/

    /**
     * @inheritdoc
     */
    public function count($q = '*', $db = null)
    {
        $this->alias('main')
             ->leftJoin(['i18n' => NewsI18n::tableName()] //!! joined here, not in search model(s)
                      , "main.id = i18n.news_id AND i18n.lang_code = '{$this->langCodeMain}'")
             ;
        return parent::count($q, $db);
    }

    /**
     * @inheritdoc
     * @return News[]|array
     */
    public function all($db = null)
    {
        $this
            ->alias('main')
            ->leftJoin(['i18n' => NewsI18n::tableName()] //!! joined here, not in search model(s)
                , "main.id = i18n.news_id AND i18n.lang_code = '{$this->langCodeMain}'")
              //, 'main.id = i18n.news_id')->where(['i18n.lang_code' => $this->langCodeMain])
            ->select([
                'main.*',
                'UNIX_TIMESTAMP(main.show_from_time) AS unix_show_from_time',
                'i18n.title AS title',
                'i18n.body AS body',
            ]);//list ($sql, $parms) = Yii::$app->db->getQueryBuilder()->build($this);var_dump($sql);var_dump($parms);
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return News|array|null
     */
    public function one($db = null)
    {
        $this->alias('main')->select([
            'main.*',
            'UNIX_TIMESTAMP(main.show_from_time) AS unix_show_from_time',
            'UNIX_TIMESTAMP(main.show_to_time) AS unix_show_to_time',
        ]);//list ($sql, $parms) = Yii::$app->db->getQueryBuilder()->build($this);var_dump($sql);var_dump($parms);
        return parent::one($db);
    }

}