<?php
/* @var $this asb\yii2\common_2_170212\web\UniView */
/* @var $dataProvider yii\data\ActiveDataProvider */

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ListView;

    $listViewId = 'news-list';
    $gridHtmlClass = 'news-list-grid';
    $gridTableClass = 'news-list-items';

    $assets = $this->context->module->registerAsset('FrontAsset', $this);

    $this->title = Yii::t($this->context->tc, 'News');
    //$this->params['breadcrumbs'][] = $this->title;
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($this->title),
        'url' => Url::to(['index']),
    ];

    //$page = $dataProvider->pagination->page + 1;var_dump($page);

?>
<?php $this->startBlock('before-page') ?>
<?php $this->stopBlock('before-page') ?>
<div class="news-list">

    <?php $this->startBlock('title') ?>
        <h1><a href="<?= Url::to(['list']) ?>"><?= Html::encode($this->title) ?></a></h1>
    <?php $this->stopBlock('title') ?>

    <?php $this->startBlock('list-view') ?>
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'id' => $listViewId,
        'options' => ['class' => $gridHtmlClass],
        'layout' => "{pager}\n<table class=\"{$gridTableClass}\">\n{items}\n</table>\n{summary}\n{pager}",

        'itemView' => function($model, $key, $index, $widget) use($dataProvider) {
            $item = $this->context->renderPartial('list-item', [
                'model' => $model,
                'key'   => $key,
                'index' => $index,
                'widget' => $widget,
                'dataProvider' => $dataProvider,
            ]);
            return '<tr class="item">' . $item . '</tr>';
        },

    ]); ?>
    <?php $this->stopBlock('list-view') ?>

</div>
<?php $this->startBlock('after-page') ?>
<?php
    // show news date-time according to client time zone from news UTC-time
    $this->registerJs("
        jQuery('.{$gridTableClass} tr td .js-time').each(function(index) {
            var elem = jQuery(this);
            var data = elem.data();
            elem.html(utcToLocalDatetime(data.unixtime));
        });
    ");

?>
<?php $this->stopBlock('after-page') ?>
