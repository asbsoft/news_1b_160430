<?php

namespace asb\yii2\modules\news_1b_160430\models;

use asb\yii2\modules\news_1b_160430\models\News;
use asb\yii2\modules\news_1b_160430\Module;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * NewsSearch represents the model behind the search form about `asb\yii2\modules\news_1b_160430\models\News`.
 */
class NewsSearch extends News
{
    public $show_from_time_begin;
    public $show_from_time_end;
    public $show_to_time_begin;
    public $show_to_time_end;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'is_visible'], 'integer'],
            [['image', 'show_from_time', 'show_to_time', 'create_time', 'update_time'], 'safe'],
            [['show_from_time_begin', 'show_from_time_end', 'show_to_time_begin', 'show_to_time_end'], 'date'
                //, 'format' => 'php:d/m/Y'],
                , 'format' => 'php:Y-m-d'],
            [['title', 'body'], 'string'],
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
    {//echo __METHOD__;var_dump($params);//exit;
        $modelNewsClassname = $this->module->model('News')->className();
        $query = $this->module->model('NewsQuery', [$modelNewsClassname]);//var_dump($query);exit;

        // for REST: select by language
        $lang_code = false;
        if (!empty($params['lang_code'])) $lang_code = $params['lang_code'];
        if (!empty($params['lang']))      $lang_code = $params['lang'];
        if (!empty($lang_code)) {
            //$config = Module::getModuleConfigByClassname(Module::className());
            //$langHelper = new $config['params']['langHelper'];
            $module = Module::getModuleByClassname(Module::className());
            $langHelper = $module->langHelper;

            if ($langHelper::isValidLangCode($lang_code)) {
                $query->langCodeMain = $langHelper::normalizeLangCode($lang_code);
            }//var_dump($query->langCodeMain);exit;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'main.id' => $this->id, //ambiguous: 'id' => $this->id,
            'owner_id' => $this->owner_id,
            'is_visible' => $this->is_visible,
            'show_from_time' => $this->show_from_time,
            'show_to_time' => $this->show_to_time,
            //'create_time' => $this->create_time,
            //'update_time' => $this->update_time,
        ]);

        //$query->alias('main')->leftJoin(['i18n' => NewsI18n::tableName()], "main.id = i18n.news_id AND i18n.lang_code = '{$this->langCodeMain}'");

        //$query->andFilterWhere(['like', 'image', $this->image]);

        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'body', $this->body]);

        $query
            ->andFilterWhere(['>=', 'show_from_time'
                , $this->show_from_time_begin ? $this->show_from_time_begin . ' 00:00:00' : null])
            ->andFilterWhere(['<=', 'show_from_time'
                , $this->show_from_time_end ? $this->show_from_time_end . ' 23:59:59' : null])
            ->andFilterWhere(['>=', 'show_to_time'
                , $this->show_to_time_begin ? $this->show_to_time_begin . ' 00:00:00' : null])
            ->andFilterWhere(['<=', 'show_to_time'
                , $this->show_to_time_end ? $this->show_to_time_end . ' 23:59:59' : null])
            ;

        $dataProvider->sort->attributes['title'] = [
            'asc'  => ['title' => SORT_ASC],
            'desc' => ['title' => SORT_DESC],
        ];

        if (empty($params['sort'])) {
            //$query->orderBy(['show_from_time' => SORT_DESC]); // default order
            $query->orderBy($this->defaultOrderBy);
        }
            
        //list($sql, $sqlParams) = Yii::$app->db->getQueryBuilder()->build($query);var_dump($sql);var_dump($sqlParams);exit;
        return $dataProvider;
    }
}
