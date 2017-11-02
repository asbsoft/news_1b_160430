<?php

namespace asb\yii2\modules\news_1b_160430\models;

use asb\yii2\common_2_170212\models\DataModel;

use Yii;

/**
 * @property integer $id
 * @property integer $news_id
 * @property string $title
 * @property string $body
 */
class NewsI18n extends DataModel //BaseDataModel
{
    //const TABLE_NAME = 'news_i18n'; // deprecated

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['news_id'], 'integer'],
            [['title'], 'trim'],
            [['title'], 'string', 'max' => 255],
            [['body'], 'string'],
        ];
        if ($this->module->params['allMultilangFieldsRequired'] ) {
            $rules[] = [['title', 'body'], 'required'];
        }
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t($this->tcModule, 'ID'),
            'news_id' => Yii::t($this->tcModule, 'News ID'),
            'title' => Yii::t($this->tcModule, 'Title'),
            'body' => Yii::t($this->tcModule, 'Body'),
        ];
    }

    /**
     * Declares a `has-one` relation.
     */
    public function getMain()
    {
        return $this->hasOne($this->module->model('News')->className(), ['id' => 'news_id']);
    }

    /**
     * @inheritdoc
     * @return NewsI18nQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NewsI18nQuery(get_called_class());
    }

}
