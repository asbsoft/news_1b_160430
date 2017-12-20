<?php

namespace asb\yii2\modules\news_1b_160430\controllers;

use asb\yii2\modules\news_1b_160430\models\NewsSearchFront;

use asb\yii2\common_2_170212\controllers\BaseMultilangController;

use Yii;
use yii\helpers\Url;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class MainController extends BaseMultilangController
{
    public $uploadsNewsUrl;

    protected $contentHelperClass;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->uploadsNewsUrl = Yii::getAlias($this->module->params['uploadsNewsUrl']);
        $this->contentHelperClass = $this->module->contentHelper;
    }

    /**
     * Start action.
     */
    public function actionIndex()
    {
        return $this->redirect(['list']);
    }

    /**
     * Prepare search parameters from query and config parameters.
     * Will add search criteria here: 'owner_id' (author), LIKE 'title'/'body'
     */
    protected function prepateListSearchParams()
    {
        $groupName = basename(NewsSearchFront::className());
        $params = [];
        $params[$groupName] = Yii::$app->request->queryParams;
        if (!empty($this->module->params['titleMinLength'])) {
            $params[$groupName]['titleMinLength'] = $this->module->params['titleMinLength'];
        }
        if (!empty($this->module->params['bodyMinLength'])) {
            $params[$groupName]['bodyMinLength'] = $this->module->params['bodyMinLength' ];
        }//var_dump($params);
        return $params;
    }

    public function actionList($page = 1)
    {
        $params = $this->prepateListSearchParams();//var_dump($params);
        $searchModel = new NewsSearchFront();
        $dataProvider = $searchModel->search($params);

        $pager = $dataProvider->getPagination();
        $pager->pageSize = $this->module->params['pageSizeFront'];
        $pager->totalCount = $dataProvider->getTotalCount();//var_dump($pager->totalCount);exit;

        // page number correction:
        if ($pager->totalCount <= $pager->pageSize || $page > ceil($pager->totalCount / $pager->pageSize) ) {
            $pager->page = 0; //goto 1st page if shortage records
        } else {
            $pager->page = $page - 1; //! from 0
        }//var_dump($page);var_dump($pager->page);

        $this->renderData = [
            'dataProvider' => $dataProvider,
        ];
        return $this->render('list', $this->renderData);
    }

    /**
     * Render article's body.
     * @param integer $id
     * @param boolean $renderPartial if true ignore visibility and show without layout by call renderPartial()
     */
    public function actionView($id, $renderPartial = false)
    {
        $modelNews = $this->module->model('News');
        $model = $modelNews::findOne($id);

        $modelI18n = false;
        if (!empty($model)) {
            $modelsI18n = $modelNews::prepareI18nModels($model);
            $modelI18n = $modelsI18n[$this->langCodeMain];
        }

        if ($renderPartial) {
            $model = NewsSearchFront::canShow($model, $modelI18n, $ignoreVisibility = true);
        } else {
            $model = NewsSearchFront::canShow($model, $modelI18n);
        }

        $this->renderData = [
            'model' => $model,
            'modelI18n' => $modelI18n,
        ];
        if ($renderPartial) {
            return $this->renderPartial('view', $this->renderData);
        } else {
            return $this->render('view', $this->renderData);
        }
    }

}
