<?php

namespace asb\yii2\modules\news_1b_160430\controllers;

use asb\yii2\modules\news_1b_160430\models\News;
use asb\yii2\modules\news_1b_160430\models\NewsI18n;
use asb\yii2\modules\news_1b_160430\models\NewsSearch;

use asb\yii2\common_2_170212\controllers\BaseAdminMulangController;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\base\ErrorException;
use yii\db\Exception as DbException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
//use yii\filters\AccessControl;

/**
 * AdminController implements the CRUD actions for News model.
 *
 * @author ASB <ab2014box@gmail.com>
 */
class AdminController extends BaseAdminMulangController
{
    public $canAuthorEditOwnVisibleArticle = false; // dafault

    public $multilangFormName;

    public $baseMainImageName = 'main';
    public $uploadsNewsUrl;
    public $uploadsNewsDir;
    public $maxImageSize;

    protected $contentHelperClass;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //$this->multilangFormName = basename(NewsI18n::className());
        $this->multilangFormName = basename($this->module->model('NewsI18n')->className());

        $this->maxImageSize = $this->module->params['maxImageSize'];
        $this->uploadsNewsUrl = Yii::getAlias($this->module->params['uploadsNewsUrl']);

        $this->uploadsNewsDir = Yii::getAlias($this->module->params['uploadsNewsDir']);
        FileHelper::createDirectory($this->uploadsNewsDir);

        $uploadsCommonDir = $this->uploadsNewsDir . '/' . $this->module->params['uploadsCommonSubdir'];
        FileHelper::createDirectory($uploadsCommonDir);

        $this->contentHelperClass = $this->module->contentHelper;

        $param = 'canAuthorEditOwnVisibleArticle';
        if (isset($this->module->params[$param])) {
            $this->$param = $this->module->params[$param];
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);//var_dump($behaviors['access']['rules']);

        $behaviors['access']['rules'] = []; // disable default 'roleRoot' and 'roleAdmin'

        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['index', 'view']
        //, 'roles' => ['roleNewsModerator', 'roleNewsAuthor'],
          , 'roles' => ['roleNewsAuthor', 'roleNewsModerator'],
        ];
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['change-visible'], 'roles' => ['roleNewsModerator']];
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['create'], 'roles' => ['createNews']];
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['delete'], 'roles' => ['deleteNews']];
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['update']
          //, 'roles' => ['updateOwnNews'] //x ? how to send params['news'] to this role -> move checking to actionUpdate()
          //, 'roles' => ['updateNews'] //x author can't edit own post
          //, 'roles' => ['roleNewsAuthor'] //x not enough - moderator can't edit nothing
            , 'roles' => ['roleNewsModerator', 'roleNewsAuthor'] //!! + check Yii::$app->user->can('...', [...]) in actionUpdate()
        ];//var_dump($behaviors['access']['rules']);exit;
        return $behaviors;
    }

    /**
     * Lists all News models.
     * @return mixed
     */
    public function actionIndex($page = 1, $id = 0)
    {
        $searchModel = new NewsSearch();
        $params = Yii::$app->request->queryParams;
        if (!Yii::$app->user->can('roleNewsModerator') && Yii::$app->user->can('roleNewsAuthor')) {
            $params[$searchModel->formName()]['owner_id'] = Yii::$app->user->id;
        }//var_dump($params);
        $dataProvider = $searchModel->search($params);

        $pager = $dataProvider->getPagination();
        $pager->pageSize = $this->module->params['pageSizeAdmin'];
        $pager->totalCount = $dataProvider->getTotalCount();

        // page number correction:
        //if ($pager->totalCount <= $pager->pageSize || $page > ceil($pager->totalCount / $pager->pageSize) ) {
        //    $pager->page = 0; //goto 1st page if shortage records
        $maxPage = ceil($pager->totalCount / $pager->pageSize);
        if ($page > $maxPage) {
            $pager->page = $maxPage - 1;
        } else {
            $pager->page = $page - 1; //! from 0
        }//var_dump($page);var_dump($pager->page);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'currentId'    => $id,
        ]);
    }

    /**
     * Displays a single News model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $modelsI18n = $model::prepareI18nModels($model);

        if ($model->pageSize > 0) {
            $model->orderBy = $model->defaultOrderBy;
            $model->page = $model->calcPage();//echo __METHOD__.": calcPage(id={$id},pageSize={$model->pageSize})={$model->page}<br>";exit;
        }
        return $this->render('view', [
            'model' => $model,
            'modelsI18n' => $modelsI18n,
        ]);
    }

    /**
     * Creates a new News model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {//echo __METHOD__."@{$this->module->className()}";
        //$model = new News();
        $model = $this->module->model('News');//var_dump($model->className());
        $model = $model->loadDefaultValues();
        //$modelsI18n = News::prepareI18nModels($model);
        $modelsI18n = $model::prepareI18nModels($model);//var_dump($modelsI18n);exit;

        $activeTab = $this->langCodeMain;

        $created = $this->savePost($model, $modelsI18n);
        if (!$created) {
            return $this->render('create', [
                'model' => $model,
                'modelsI18n' => $modelsI18n,
                'activeTab' => $activeTab,
            ]);
        } else if ($activeTab = $this->modelsHaveErrors($model, $modelsI18n)) { // main record created but exist another errors
            Yii::$app->session->setFlash('warning', Yii::t($this->tcModule, 'Record create but partially'));
            if ($activeTab === true) $activeTab = $this->langCodeMain;
            $errors = $model->errors;
            foreach($modelsI18n as $langCode => $modelI18n) {
                if ($modelI18n->hasErrors()) {
                    $errors += $modelI18n->errors;
                    $activeTab = $langCode;
                    break;
                }
            }
            $error = array_shift($errors); // get only first error
            if (isset($error[0])) {
                Yii::$app->session->setFlash('error', $error[0]);
            }
            return $this->redirect(['update',
                'id' => $model->id,
                'activeTab' => $activeTab,
            ]);
        } else {
            Yii::$app->session->setFlash('success', Yii::t($this->tcModule, 'Record create success'));
            if ($model->aftersave == $model::AFTERSAVE_VIEW) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->redirect(['index',
                    'page' => $model->page,
                    'id' => $model->id,
                    'sort' => $model->orderBy,
                    //'lang' => Yii::$app->language,
                ]);
            }
        }
    }

    /**
     * Updates an existing News model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if user can't edit this news
     */
    public function actionUpdate($id, $activeTab = null)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('roleNewsModerator') // user is not moderator
         &&  Yii::$app->user->can('roleNewsAuthor')    // but is author ...
         && !Yii::$app->user->can('updateOwnNews', [   // ... can edit only own post
                'news' => $model,                      //     - if post unvisible - always can
                'canEditVisible'                       //     - but if not visible - see this param
                    => $this->canAuthorEditOwnVisibleArticle,
            ])
        ) {
            if ($this->canAuthorEditOwnVisibleArticle) {
                throw new ForbiddenHttpException(Yii::t($this->tc, 'You can update only your own post'));
            } else {
                throw new ForbiddenHttpException(Yii::t($this->tc, 'You can update only your own post still unvisible'));
            }
        }

        //$modelsI18n = News::prepareI18nModels($model);
        $modelsI18n = $this->module->model('News')->prepareI18nModels($model);

        if (empty($activeTab)) $activeTab = $this->langCodeMain;

        $created = $this->savePost($model, $modelsI18n);

        if (!$created || ($activeTab = $this->modelsHaveErrors($model, $modelsI18n))) {
            return $this->render('update', [
                'model' => $model,
                'modelsI18n' => $modelsI18n,
                'activeTab' => $activeTab,
            ]);
        } else {
            Yii::$app->session->setFlash('success', Yii::t($this->tcModule, 'Record save success'));
            if ($model->aftersave == $model::AFTERSAVE_VIEW) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->redirect(['index',
                    'page' => $model->page,
                    'id' => $model->id,
                    'sort' => $model->orderBy,
                    //'lang' => Yii::$app->language,
                ]);
            }
        }
    }

    /** Correct datetime field according to timezone */
    protected function timeCorrection($model, $attribute)
    {//echo __METHOD__."(model,'$attribute')";var_dump($model->$attribute);
        $resultFormat = 'Y-m-d H:i';
        $dateFormats = [
            'Y-m-d H:i',
            'Y-m-d G:i',
            'Y-m-j H:i',
            'Y-m-j G:i',
            'Y-n-d H:i',
            'Y-n-d G:i',
            'Y-n-j H:i',
            'Y-n-j G:i',
            //'Y-m-d', 'Y-m-j', 'Y-n-d', 'Y-n-j', // do not use - will add current time, not 00:00
        ];

        // correct if empty time
        //$model->$attribute = rtrim($model->$attribute, " :");
        $needCorrection = true;
        if (':' == substr($model->$attribute, -1)) {
            $model->$attribute = rtrim($model->$attribute, ':');
            $model->$attribute .= '00:00';
            $needCorrection = false;
        }

        foreach($dateFormats as $dateFormat) {
            $datetime = \DateTime::createFromFormat($dateFormat, $model->$attribute);
            if (!empty($datetime)) break;

        }
        if ($needCorrection && !empty($datetime) && !empty($model->timezoneshift)) {
            $unixDatetime = $datetime->getTimestamp();
            $unixDatetime += 60 * intval($model->timezoneshift);
            $datetime->setTimestamp($unixDatetime);
            $model->$attribute = $datetime->format($resultFormat);
        }//var_dump($model->$attribute);exit;
    }

    /**
     * Check all models for errors
     * @return boolean true if create at least record in main table
     */
    protected function savePost($model, $modelsI18n)
    {
        $post = Yii::$app->request->post();//var_dump($post);exit;
        if (!$model->load($post)) return false;//var_dump($model->attributes);

        $this->timeCorrection($model, 'show_from_time');
        $this->timeCorrection($model, 'show_to_time');//var_dump($model->attributes);exit;
        
        $created = false;
        try {
            if ($model->save()) {
                $created = true;

                $model->imagefile = UploadedFile::getInstance($model, 'imagefile');
                if ($model->imagefile && $model->validate()) {                
                    $model->image = $this->saveImage($model);
                    if($model->image) $model->save(false, ['image']);
                }//var_dump($model->attributes);var_dump($model->errors);exit;

                $contentHelper = new $this->contentHelperClass;
                foreach($modelsI18n as $langCode => $modelI18n) {
                    if (!empty($post[$this->multilangFormName][$langCode])) {
                        $data[$this->multilangFormName] = $post[$this->multilangFormName][$langCode];
                        if ($modelI18n->load($data)) {
                            $modelI18n->lang_code = $langCode;
                            $modelI18n->news_id = $model->id;
                            $modelI18n->body = $contentHelper::beforeSaveBody($modelI18n->body);
                            $modelI18n->save();
                        }
                    }
                }
            }//var_dump($model->attributes);var_dump($model->errors);exit;
        } catch (DbException $e) {
            $msg = Yii::t($this->tcModule, 'Database save error');
            $msgFull = $msg . "<br />\naction=" . $this->action->uniqueId . "<br />\n" . $e->getMessage();
            Yii::error($msgFull);
            Yii::$app->session->setFlash('error', $this->module->params['showAdminSqlErrors'] ? $msgFull : $msg);
        }

        // back texts correction for visual editor
        if (!$created || $this->modelsHaveErrors($model, $modelsI18n)) {
            $modelsI18n = $model::correctI18nBodies($modelsI18n);
        }

        return $created;
    }

    /**
     * Check all models for errors
     * @return boolean|string - false if all OK, true if error in main model or language code of fail i18n-model
     */
    protected function modelsHaveErrors($model, $modelsI18n)
    {
        if ($model->hasErrors()) return true;

        $existsOneLanguageData = false;
        foreach($modelsI18n as $langCode => $modelI18n) {
            if ($modelI18n->hasErrors()) return $langCode;
            if (!empty($modelI18n->title) && !empty($modelI18n->body)) {
                $existsOneLanguageData = true;
            }
        }
        if (!$existsOneLanguageData) {
            Yii::$app->session->setFlash('error', Yii::t($this->tcModule, 'Need texts for at least one language'));
            return true;
        }
        return false;
    }

    /**
     * Save image file. Replace if exists.
     * @return string relative path to file without main upload dir prefix
     */
    protected function saveImage($model)
    {
        //$subdir = News::getImageSubdir($model->id);
        $subdir = $this->module->model('News')->getImageSubdir($model->id);
        FileHelper::createDirectory($this->uploadsNewsDir . '/' . $subdir);

        //$baseMainImageName = $model->imagefile->baseName; // don't use original file name
        $baseMainImageName = $model->id . '-' . $this->baseMainImageName; // image name should be standard
        $path = $subdir . '/' . $baseMainImageName . '.' . $model->imagefile->extension;
        $newImagePath = $this->uploadsNewsDir . '/' . $path;
        $oldImagePath = $this->uploadsNewsDir . '/' . $model->image;

        if ($newImagePath !== $oldImagePath && is_file($newImagePath)) { // names collision
            //! do not overwrite: it can be file uploaded by image manager
            //$model->addError('imagefile', 'Such image already exists: ' . $newImagePath); return false; //error can't solve problem
            rename($newImagePath, $newImagePath . '-' . uniqid()); // rename exists old file with 'new' name for save
        }

        if (is_file($oldImagePath)) {
            @unlink($oldImagePath); // always delete old main news image - need when change file extension
        }

        try {
            $model->imagefile->saveAs($newImagePath);
        } catch(ErrorException $e) {
            $model->addError('imagefile', $error = $e->getMessage());
            $path = false;
        }
        return $path;
    }

    /**
     * Deletes an existing News model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id, $page = 1)
    {
        $model = $this->findModel($id);
/* moved to model:
        $subdir = $this->module->model('News')->getImageSubdir($model->id);
        $fullDir = $this->uploadsNewsDir . '/' . $subdir;//var_dump($fullDir);exit;
        if (is_dir($fullDir)) {
            @FileHelper::removeDirectory($fullDir);
        }
*/
        $model->delete(); // images and i18n-deletions also in main model

        Yii::$app->session->setFlash('success', Yii::t($this->tcModule, 'Record with ID={id} delete success', ['id' => $id]));

        $searchFormName = basename(NewsSearch::className());
        $paramSort = Yii::$app->request->get($searchFormName, []);
        foreach ($paramSort as $key => $val) {
            if (empty($val)) unset($paramSort[$key]);
        }
        return $this->redirect(['index',
            'page' => $page,
            $searchFormName => $paramSort,
            'sort' => $model->orderBy,
        ]);
    }

    /**
     * Finds the News model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return News the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        //$model = News::findOne($id);
        $model = $this->module->model('News')->findOne($id);
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t($this->tc,'The requested news does not exist'));
        }
    }

    /**
     * Change is_visible attribute for item with $id
     * @param integer $id
     * @param integer $page
     */
    public function actionChangeVisible($id, $page = 1)
    {
        $model = $this->findModel($id);//var_dump($model->attributes);
        $model->is_visible = $model->is_visible ? false: true;

        $params = Yii::$app->request->getQueryParams();//var_dump($params);exit;
        $paramSort = isset($params['sort']) ? $params['sort'] : null;
/*
        // set $model->orderBy for calculate $model->page
        $searchModel = new NewsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $sort = $dataProvider->getSort();
        $model->orderBy = $sort->getAttributeOrders();//var_dump($model->orderBy);exit;
*/
        $model->save();//var_dump($model->errors);exit;

        return $this->redirect(['index',
            //'page' => $model->page, //!! $model->page illegal when sort field not unique
            'page' => $page,
            'id' => $model->id,
            'sort' => $paramSort,
            //'errors' => $model->errors,
        ]);
    }

}
