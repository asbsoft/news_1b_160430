<?php

namespace asb\yii2\modules\news_1b_160430\models;

use asb\yii2\modules\news_1b_160430\Module;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * NewsSearch represents the model behind the search form about `asb\yii2\modules\news_1b_160430\models\News`.
 */
class NewsSearchFront extends News
{
    public $titleMinLength;
    public $bodyMinLength;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'is_visible'], 'integer'],
            [['title', 'body'], 'string'],
            [['titleMinLength', 'bodyMinLength'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {//echo __METHOD__;var_dump($params);
        $this->load($params);//var_dump($this->titleMinLength);var_dump($this->title);var_dump($this->body);

        $modelNewsClassname = $this->module->model('News')->className();
        $query = $this->module->model('NewsQuery', [$modelNewsClassname]);//var_dump($query);exit;

        $query->orderBy(['show_from_time' => SORT_DESC]);

        //$query->alias('main')->leftJoin(['i18n' => NewsI18n::tableName()], "main.id = i18n.news_id AND i18n.lang_code = '{$this->langCodeMain}'");

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            $query->where('0=1'); // uncomment this line if you do not want to return any records when validation fails
            return $dataProvider;
        }

        // required cliterias
        $query->where(['is_visible' => true]);

        $tzShiftSec = intval(date('Z')); // server time zone shift in seconds: west UTC <0, east UTC >0
        if (!empty($tzShiftSec)) {
            $now = new Expression("DATE_SUB(NOW(), INTERVAL {$tzShiftSec} SECOND)"); // UTC time
        }
        $query->andWhere(['<=', 'show_from_time', $now])
              ->andWhere(['or',
                  ['>=', 'show_to_time', $now],
                  ['show_to_time' => null],
                  //new Expression("show_to_time IS NULL"), //?? ['show_to_time' => null],
              ]);
        // no news for current language
/*
        $query->andWhere(['not', ['title' => null]]);
        $query->andWhere(['not', ['body'  => null]]);
        $query->andWhere(['not', ['title' => ''  ]]);
        $query->andWhere(['not', ['body'  => ''  ]]);
*/
        $query->andWhere(['and',
          ['not', ['title' => null]],
          ['not', ['body'  => null]],
          ['not', ['title' => ''  ]],
          ['not', ['body'  => ''  ]],
        ]);
        if (isset($this->titleMinLength)) {
            //$lengthExpr = new Expression("LENGTH(title)"); //! length in bytes, not UTF8-symbols
            $lengthExpr = new Expression("CHAR_LENGTH(title)");
            $query->andFilterWhere(['>=', $lengthExpr, $this->titleMinLength]);
        }
        if (isset($this->bodyMinLength)) {
            //$lengthExpr = new Expression("LENGTH(body)"); //! length in bytes, not UTF8-symbols
            $lengthExpr = new Expression("CHAR_LENGTH(body)");
            $query->andFilterWhere(['>=', $lengthExpr, $this->bodyMinLength]);
        }

        // additional search criterias
        $query->andFilterWhere(['owner_id' => $this->owner_id]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'body', $this->body]);

        //list($sql, $sqlParams) = Yii::$app->db->getQueryBuilder()->build($query);var_dump($sql);var_dump($sqlParams);
        return $dataProvider;
    }

    /**
     * Check if record representing this model can show: visibility, time, etc.
     * @param News $model
     * @param NewsI18n $modelI18n
     * @return $model|false
     */
    public static function canShow($model, $modelI18n)
    {
        if (empty($model) || empty($modelI18n)) return false;//var_dump($model->attributes);var_dump($modelI18n->attributes);

        if (!$model->is_visible) return false;

        if (empty($modelI18n->title) || empty($modelI18n->body)) return false;

        if (mb_strlen($modelI18n->title, 'UTF-8') < $model->module->params['titleMinLength']) return false;
        if (mb_strlen($modelI18n->body, 'UTF-8') < $model->module->params['bodyMinLength']) return false;

        $tzShiftSec = intval(date('Z')); // server time zone shift in seconds: west UTC <0, east UTC >0
        $serverUtcTime = time() - $tzShiftSec;//var_dump($serverUtcTime);
        //var_dump($model->unix_show_from_time);
        if ($serverUtcTime < $model->unix_show_from_time) return false;
        //var_dump($model->unix_show_to_time);
        if (!empty($model->unix_show_to_time) && $model->unix_show_to_time < $serverUtcTime) return false;

        return $model;
    }

}
