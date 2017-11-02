<?php
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */

    //use asb\yii2\modules\news_1b_160430\models\Formatter;
    //use asb\yii2\modules\news_1b_160430\assets\FrontAsset;

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ListView;

    $listViewId = 'news-list';
    $gridHtmlClass = 'news-list-grid';
    $gridTableClass = 'news-list-items';

    //$assets = FrontAsset::register($this);
    $assets = $this->context->module->registerAsset('FrontAsset', $this);//var_dump($assets);

    $this->title = Yii::t($this->context->tc, 'News');
    //$this->params['breadcrumbs'][] = $this->title;
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($this->title),
        'url' => Url::to(['index']),
    ];

    //$page = $dataProvider->pagination->page + 1;var_dump($page);
    //var_dump($dataProvider->pagination->totalCount);
    //var_dump($dataProvider->models);
    //var_dump($this->context->tc);
    //var_dump(get_object_vars($this->context));

?>
<div class="news-list">

    <h1><a href="<?= Url::to(['list']) ?>"><?= Html::encode($this->title) ?></a></h1>

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

</div>

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
