<?php
/**
    @var $model
    @var $key
    @var $index
    @var $widget
    @var $dataProvider
*/

    //use asb\yii2\modules\news_1b_160430\assets\FrontAsset;

    use yii\helpers\Html;

    //$assets = FrontAsset::register($this);
    $assets = $this->context->module->registerAsset('FrontAsset', $this);//var_dump($assets);

//$page = $dataProvider->pagination->page + 1;var_dump($page);

?>

   <td class="item-image">
       <?php if(!empty($model->image)): ?>
           <?= Html::img($this->context->uploadsNewsUrl . '/' . $model->image, [
                   'class' => 'thumbnail',
               ]); ?>
       <?php else: ?>
           <img class="thumbnail" src="<?= $assets->baseUrl ?>/img/no-picture.jpg" />
       <?php endif; ?>
   </td>

   <td>&nbsp;</td>

   <td class="item-time">
       <div class="js-time" data-unixtime="<?= $model->unix_show_from_time ?>"><?= $model->show_from_time ?></div>
       <?= ''//$model->show_from_time ?>
   </td>

   <td>&nbsp;</td>

   <td class="item-title">
        <?= Html::a(Html::encode($model->title)
              , ['view', 'id' => $model->id
                  //, 'page' => $page
                ]
              , ['title' => $model->title]
            ); ?>
   </td>
