<?php

namespace asb\yii2\modules\news_1b_160430\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[NewsI18n]].
 *
 * @see NewsI18n
 */
class NewsI18nQuery extends ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return NewsI18n[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return NewsI18n|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}