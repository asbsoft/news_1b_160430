<?php
    /* @var $model asb\yii2\modules\news_1b_160430\models\News */
    /* @var $modelsI18n array of asb\yii2\modules\news_1b_160430\models\NewsI18n */
    /* @var $this yii\web\View */
    /* @var $activeTab string */

    use yii\helpers\Html;

    //$this->title = Yii::t($this->context->tcModule, 'Update {modelClass}: ', [
    //    'modelClass' => 'News',
    //]) . ' ' . $model->id;
    $this->title = Yii::t($this->context->tcModule, 'Update News (ID={id})', ['id' => $model->id]);

    $this->params['breadcrumbs'][] = ['label' => Yii::t($this->context->tcModule, 'News'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = ['label' => Yii::t($this->context->tcModule, 'ID') . $model->id, 'url' => ['view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = Yii::t($this->context->tcModule, 'Update');

?>
<div class="news-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelsI18n' => $modelsI18n,
        'activeTab' => $activeTab,
    ], $this->context) ?>

</div>
