<?php

namespace asb\yii2\modules\news_1b_160430\controllers\rest;

use asb\yii2\modules\news_1b_160430\models\News;

use Yii;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\rest\CreateAction as RestCreateAction;

class CreateAction extends RestCreateAction
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $modelClass = $this->modelClass;
        /* @var $model \yii\db\ActiveRecord */
        $model = new $modelClass([
            'scenario' => $this->scenario,
        ]);

        $bodyParams = Yii::$app->getRequest()->getBodyParams();//var_dump($bodyParams);
        //$formName = $model->formName();var_dump($formName);
        //$bodyParams[$model->formName()] = $bodyParams; // not need
        $model->load($bodyParams, ''); // $formName = ''

        $userId = $this->controller->getUserId();
        if (empty($userId)) return null;
        $model->owner_id = $userId;
        $model->create_time = new Expression('NOW()');

        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);

            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));

            // here $model does not have i18n-parts need for return correct result
            $model = $modelClass::findOne($model->id); // reload this model
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }
}
