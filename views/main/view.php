<?php
/* @var $model asb\yii2\modules\news_1b_160430\models\News|empty */
/* @var $modelI18n asb\yii2\modules\news_1b_160430\models\NewsI18n|empty */

    //use asb\yii2\modules\news_1b_160430\assets\FrontAsset;
    use asb\yii2\modules\news_1b_160430\models\News;

    use yii\helpers\Html;
    use yii\helpers\Url;

    $heightImage = 100;

    //$assets = FrontAsset::register($this);
    $assets = $this->context->module->registerAsset('FrontAsset', $this);//var_dump($assets);

    if (empty($modelI18n)) $model = false;

    //echo mb_strlen($modelI18n->body, 'UTF-8') . '/' . strlen($modelI18n->body);

    //var_dump($this->context->module->layoutPath);
?>

<div class="news-view">

    <?php if (empty($model)): ?>
        <h1><?= Yii::t($this->context->tc, 'Such news not found') ?></h1>
    <?php else: ?>

       <div class="js-time" data-unixtime="<?= $model->unix_show_from_time ?>"><?= $model->show_from_time ?></div>

        <h1><?= Html::encode($modelI18n->title) ?></h1>

        <?php
            if(!empty($model->image)) {
                echo Html::img($this->context->uploadsNewsUrl . '/' . $model->image, [
                    'height' => $heightImage,
                    'class' => 'news-header-image',
                ]);
            }
        ?>

        <?= $modelI18n->body ?>

    <?php endif; ?>
</div>

<?php
    // show news date-time according to client time zone from news UTC-time
    $this->registerJs("
        jQuery('.js-time').each(function(index) {
            var elem = jQuery(this);
            var data = elem.data();
            elem.html(utcToLocalDatetime(data.unixtime));
        });
    ");
?>
