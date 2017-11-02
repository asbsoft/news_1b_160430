<?php

namespace asb\yii2\modules\news_1b_160430\controllers;

use asb\yii2\modules\news_1b_160430\models\News;
use asb\yii2\modules\news_1b_160430\models\NewsSearch;

use asb\yii2\modules\news_1b_160430\Module;
use asb\yii2\modules\news_1b_160430\controllers\rest\Serializer;

use asb\yii2\modules\news_1b_160430\controllers\rest\CreateAction;

use yii\rest\UpdateAction;//use asb\yii2\modules\news_1b_160430\controllers\rest\UpdateAction;
use yii\rest\ViewAction;//use asb\yii2\modules\news_1b_160430\controllers\rest\ViewAction;
use yii\rest\IndexAction;
use yii\rest\ActiveController;
use yii\filters\auth\QueryParamAuth;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\base\InvalidParamException;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use Yii;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class RestController extends ActiveController
{
    public $pageSize = 10; // default if not define in config

    public $config;
    public $langCodeMain;
    public $languages;

    public $restLangAreaTag = '_languages_';

    protected $userIdentity;
    protected $contentHelperClass;

    /**
     * @inheritdoc
     */
    public function init()
    {
        //parent::init(); //!! throw exception here
        $this->modelClass = News::className();
        parent::init();

        $langHelper = $this->module->langHelper;
        $this->langCodeMain = $langHelper::normalizeLangCode(Yii::$app->language);
        $this->languages = $langHelper::activeLanguages();//var_dump($this->languages);exit;

        if (!empty($this->module->params['restLangAreaTag'])) {
            $this->restLangAreaTag = $this->module->params['restLangAreaTag'];
        }
        
        if (!empty($this->module->params['pageSizeRest']) && intval($this->module->params['pageSizeRest']) > 0) {
            $this->pageSize = intval($this->module->params['pageSizeRest']);
        }

        if (empty($this->module->userIdentity)) {
            throw new InvalidParamException("This module config must have 'userIdentity' parameter");
        }

        $this->userIdentity = new $this->module->userIdentity;
        $this->contentHelperClass = $this->module->contentHelper;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        $actions['create'] = [
            'class' => CreateAction::className(),
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario' => $this->createScenario,
        ];
/*
        $actions['update'] = [
            'class' => UpdateAction::className(),
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario' => $this->updateScenario,
        ];
*/
        $actions['view'] = [
            'class' => ViewAction::className(),
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
/* move to ViewAction:
            'findModel' => function ($id, $action) {
                $modelClass = $this->modelClass;
                $model = $modelClass::findOne($id);//var_dump($model->attributes);
                $modelsI18n = $modelClass::prepareI18nModels($model);//var_dump($modelsI18n);
                $modelI18n = $modelsI18n[$this->langCodeMain];//var_dump($modelI18n->attributes);
                //...??
                return $model;
            },
*/
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();//var_dump($behaviors);

        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];

        if (empty($behaviors['access'])) {
            $behaviors['access'] = ['class' => AccessControl::className()];
        }
      //$behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['roleNewsModerator', 'roleNewsAuthor']]; //?? wrong
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['roleNewsAuthor', 'roleNewsModerator']];
      //$behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['change-visible'], 'roles' => ['roleNewsModerator']]; // not support
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['create'], 'roles' => ['createNews']];
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['delete'], 'roles' => ['deleteNews']];
        $behaviors['access']['rules'][] = ['allow' => true, 'actions' => ['update'], 'roles' => ['roleNewsModerator', 'roleNewsAuthor']];
/*
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => array_keys($this->languages),
        ];
/**/
        //var_dump($behaviors);exit;
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($action, $model = null, $params = [])
    {//echo __METHOD__;var_dump($action);var_dump($model);exit;

        //$userId = Yii::$app->user->identity->id;//var_dump($userId);//exit;
        $userId = $this->getUserId();//var_dump($userId);exit;
        $authManager = Yii::$app->authManager;
        //$isAdmin = $authManager->checkAccess($userId, 'roleAdmin');//var_dump($isAdmin);exit;
        $isModerator = $authManager->getAssignment('roleNewsModerator', $userId);//var_dump($isModerator);
        $isAuthor = $authManager->getAssignment('roleNewsAuthor', $userId);//var_dump($isAuthor);

        switch ($action) {
            case 'index':
                //var_dump($model);//?? null
                break;
            case 'delete':
                if (empty($isModerator)) {
                    throw new ForbiddenHttpException("You can't delete post");
                }            
                break;
            case 'create':
                if (empty($isAuthor)) {
                    throw new ForbiddenHttpException('Only authors can create post');
                }            
                break;
            case 'view':
            case 'update'://var_dump($model->owner_id);var_dump($userId);exit;
                if (empty($isModerator) && !empty($model) && $model->owner_id != $userId) {
                    throw new ForbiddenHttpException("You can't get alien post");
                }            
                break;
            default:
                throw new ForbiddenHttpException('Unknown action');
        }
    }

    /**
     * Get user id by access token.
     * @return integer|false user id or false if auth fail
     */
    public function getUserId()
    {
        $params = Yii::$app->request->queryParams;
        if (empty($params['access-token'])) return false;
        $uid = $this->userIdentity->findIdentityByAccessToken($params['access-token']);
        if (empty($uid->id)) return false;
        else return $uid->id;
    }
    
    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {//echo __METHOD__;
        $params = Yii::$app->request->queryParams;//var_dump($params);exit;

        $userId = $this->getUserId();//var_dump($userId);exit;
        if (empty($userId)) return null;

        $authManager = Yii::$app->authManager;
        //$asns = $authManager->getAssignments($userId);//var_dump($asns);exit;
        $isModerator = $authManager->getAssignment('roleNewsModerator', $userId);//var_dump($isModerator);
        $isAuthor = $authManager->getAssignment('roleNewsAuthor', $userId);//var_dump($isAuthor);
        if (empty($isModerator)) {
            if (empty($isAuthor)) throw new ForbiddenHttpException('Unsupported role');
            else $params['owner_id'] = $userId; // author get only his articles
        }

        $modelSearch = new NewsSearch();
        $params[$modelSearch->formName()] = [
            'user_id' => $userId, // required
        ];
        foreach (array_keys($modelSearch->attributes) as $field) {
            if (array_key_exists($field, $params)) {
                $params[$modelSearch->formName()][$field] = $params[$field];
            }
        }

        if (!empty($params['title'])) $params[$modelSearch->formName()]['title'] = $params['title'];
        if (!empty($params['body']))  $params[$modelSearch->formName()]['body']  = $params['body'];

/* sort-parameter will process not here (!?)
        if (isset($params['sort'])) {
            if (empty($params['sort'])) { // '&sort=' exists but empty
                $params['sort'] = false; // false means unsorted
            } else {
                $sort = $params['sort'];
                $first = substr($sort, 0, 1);
                if (in_array($first, ['-', '+', ' '])) { // '%2B' -> '+', '+' -> ' '
                    $sortField = substr($sort, 1);
                    $sortDir = $first == '-' ? SORT_DESC : SORT_ASC;
                } else {
                    $sortField = $sort;
                    $sortDir = SORT_ASC;
                }
                if (array_key_exists($sortField, $modelSearch->attributes)) {
                    $params['sort'] = ['defaultOrder' => [$sortField => $sortDir]];
                } else {
                    unset($params['sort']);
                }
            }
        } else {
            // default reverse sort
            $params['sort'] = ['defaultOrder' => ['show_from_time' => SORT_DESC]];
            //$params['sort'] = ['defaultOrder' => ['id' => SORT_DESC]];
        }
*/

        //var_dump($params);exit;
        $dataProvider = $modelSearch->search($params);

        $page = empty($params['page']) ? 1 : intval($params['page']);
        if ($page == 0) $page = 1;
        $pager = $dataProvider->getPagination();
        $pager->pageSize = $this->pageSize;
        $pager->totalCount = $dataProvider->getTotalCount();
        $pager->page = $page - 1; //! from 0

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {//echo __METHOD__;var_dump($action::className());//var_dump($result);
        $result0 = $result;
        switch ($action::className()) {
            case ViewAction::className():
            case CreateAction::className(): // ? after create i18n-parts empty - fixed by reload model in CreateAction
            case UpdateAction::className():
                $result = parent::afterAction($action, $result);//var_dump($result);exit;
                $modelClass = $this->modelClass;
                $modelsI18n = $modelClass::prepareI18nModels($result0);//var_dump($modelsI18n);exit;
                $result[$this->restLangAreaTag] = [];
                foreach (array_keys($this->languages) as $langCode) {
                    $modelI18n = $modelsI18n[$langCode];
                    $result[$this->restLangAreaTag][$langCode] = $this->serializeData($modelI18n);
                }
                break;
/*
            case CreateAction::className():
                //?? $result0 does not have i18n-data
                $result = parent::afterAction($action, $result);//var_dump($result);exit;
                if (!empty($result['id'])) {
                    $modelClass = $this->modelClass;
                    $model = $modelClass::findOne($result['id']); //?? this $model has i18n-data, but $result0 does not
                                                                  //!! move this to controllers\rest\CreateAction
                    $modelsI18n = $modelClass::prepareI18nModels($model);//var_dump($modelsI18n);exit;
                    $result[$this->restLangAreaTag] = [];
                    foreach (array_keys($this->languages) as $langCode) {
                        $modelI18n = $modelsI18n[$langCode];
                        $result[$this->restLangAreaTag][$langCode] = $this->serializeData($modelI18n);
                    }
                }               
                break;
*/
            case IndexAction::className():
                //var_dump($result0->models);
                $serializer = Yii::createObject(Serializer::className());
                $result = $serializer->serialize($result0);
                break;
            default;
                $result = parent::afterAction($action, $result);//var_dump($result);exit;
                break;
        }//var_dump($result);exit;
        return $result;
    }

}
