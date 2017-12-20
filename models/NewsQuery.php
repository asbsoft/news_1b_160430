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
    public $tableAliasMain = 'main';
    public $tableAliasI18n = 'i18n';

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
        $this->alias($this->tableAliasMain)
             ->leftJoin([$this->tableAliasI18n => NewsI18n::tableName()] //!! joined here, not in search model(s)
                      , "{$this->tableAliasMain}.id = {$this->tableAliasI18n}.news_id"
                        . " AND {$this->tableAliasI18n}.lang_code = '{$this->langCodeMain}'")
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
            ->alias($this->tableAliasMain)
            ->leftJoin([$this->tableAliasI18n => NewsI18n::tableName()] //!! joined here, not in search model(s)
                , "{$this->tableAliasMain}.id = {$this->tableAliasI18n}.news_id"
                  . " AND {$this->tableAliasI18n}.lang_code = '{$this->langCodeMain}'")
              //, "{$this->tableAliasMain}.id = {$this->tableAliasI18n}.news_id")->where(["{$this->tableAliasI18n}.lang_code" => $this->langCodeMain])
            ->select([
                "{$this->tableAliasMain}.*",
                "UNIX_TIMESTAMP({$this->tableAliasMain}.show_from_time) AS unix_show_from_time",
                "{$this->tableAliasI18n}.title AS title",
                "{$this->tableAliasI18n}.body AS body",
            ]);//list ($sql, $parms) = Yii::$app->db->getQueryBuilder()->build($this);var_dump($sql);var_dump($parms);
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return News|array|null
     */
    public function one($db = null)
    {
        $this->alias($this->tableAliasMain)->select([
            "{$this->tableAliasMain}.*",
            "UNIX_TIMESTAMP({$this->tableAliasMain}.show_from_time) AS unix_show_from_time",
            "UNIX_TIMESTAMP({$this->tableAliasMain}.show_to_time) AS unix_show_to_time",
        ]);//list ($sql, $parms) = Yii::$app->db->getQueryBuilder()->build($this);var_dump($sql);var_dump($parms);
        return parent::one($db);
    }

}