<?php
/* @var $model asb\yii2\modules\news_1b_160430\models\News|empty */
/* @var $modelI18n asb\yii2\modules\news_1b_160430\models\NewsI18n|empty */

  //use asb\yii2\modules\news_1b_160430\assets\FrontAsset;
    use asb\yii2\modules\news_1b_160430\models\News;

    use yii\helpers\Html;
    use yii\helpers\Url;

    $heightImage = 100;


    $assets = $this->context->module->registerAsset('FrontAsset', $this); // instead of $assets = FrontAsset::register($this);

    if (empty($modelI18n)) {
        $model = false;
        $this->title = Yii::t($this->context->tc, 'Such news not found') . ' - ' . Yii::t($this->context->tc, 'News');
    } else {
        $this->title = $modelI18n->title . ' - ' . Yii::t($this->context->tc, 'News');
    }

?>
<div class="news-view">

    <?php if (empty($model)): ?>
        <h1><?= Yii::t($this->context->tc, 'Such news not found') ?></h1>
    <?php else: ?>
        <?php $this->startBlock('article') ?>

            <?php $this->startBlock('datetime') ?>
                <div class="js-time" data-unixtime="<?= $model->unix_show_from_time ?>"><?= $model->show_from_time ?></div>
            <?php $this->stopBlock('datetime') ?>

            <?php $this->startBlock('title') ?>
                <h1><?= Html::encode($modelI18n->title) ?></h1>
            <?php $this->stopBlock('title') ?>

            <?php $this->startBlock('subtitle') ?>
            <?php $this->stopBlock('subtitle') ?>

            <?php $this->startBlock('image') ?>
                <?php
                    if(!empty($model->image)) {
                        echo Html::img($this->context->uploadsNewsUrl . '/' . $model->image, [
                            'height' => $heightImage,
                            'class' => 'news-header-image',
                        ]);
                    }
                ?>
            <?php $this->stopBlock('image') ?>

            <?php $this->startBlock('body') ?>
                <?= $modelI18n->body ?>
            <?php $this->stopBlock('body') ?>

        <?php $this->stopBlock('article') ?>
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
