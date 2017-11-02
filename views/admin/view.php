<?php
/* @var $this yii\web\View */
/* @var $model asb\yii2\modules\news_1b_160430\models\News */
/* @var $modelsI18n array of asb\yii2\modules\news_1b_160430\models\NewsI18n */
/* @var $page integer */

use asb\yii2\modules\news_1b_160430\models\News;
use asb\yii2\modules\news_1b_160430\models\NewsSearchFront;

//use asb\yii2\modules\news_1b_160430\assets\AdminAsset;
use asb\yii2\common_2_170212\assets\CommonAsset;
use asb\yii2\common_2_170212\assets\FlagAsset;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

    //$assetsSys = CommonAsset::register($this);
    $assetsFlag = FlagAsset::register($this);
    //$assets = AdminAsset::register($this);
    $assets = $this->context->module->registerAsset('AdminAsset', $this);//var_dump($assets);

    $this->title = Yii::t($this->context->tc, 'News #{id}', ['id' => $model->id]);
    $this->params['breadcrumbs'][] = ['label' => Yii::t($this->context->tcModule, 'News'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $lh = $this->context->module->langHelper;
    $editAllLanguages = empty($this->context->module->params['editAllLanguages'])
                      ? false : $this->context->module->params['editAllLanguages'];
    $languages = $lh::activeLanguages($editAllLanguages);//var_dump(array_keys($languages));

    $activeTab = $this->context->langCodeMain;
    $actionViewUid = $this->context->module->uniqueId . '/main/view';//var_dump($actionViewUid);

?>
<div class="news-admin-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('yii', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('yii', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t($this->context->tc, 'Return to list'), ['index'
              , 'page' => $model->page, 'id' => $model->id
            ], ['class' => 'btn btn-success']) ?>
    </p>
    
    <div class="tabbable news-lang-switch">
        <ul class="nav nav-tabs">
            <?php // multi-lang part - tabs
                foreach ($languages as $langCode => $lang):
                    $countryCode2 = strtolower(substr($langCode, 3, 2));
            ?>
                <li class="<?php if ($activeTab == $langCode): ?>active<?php endif; ?>">
                    <div class="tab-field">
                        <div class="tab-link flag f16">
                            <a href="#tab-<?= $langCode ?>" data-toggle="tab"><?= $lang->name_orig ?></a>
                            <span class="flag <?= $countryCode2 ?>" title="<?= "{$lang->name_orig}" ?>"></span>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <?php // multi-lang part - content
              foreach ($languages as $langCode => $lang):
                  $countryCode2 = strtolower(substr($langCode, 3, 2));
                  $flag = '<span class="flag f16"><span class="flag ' . $countryCode2 . '" title="' . $lang->name_orig . '"></span></span>';
                  $labels = $modelsI18n[$langCode]->attributeLabels();
                  //var_dump($modelsI18n[$langCode]->attributes);
                  $modelI18n = $modelsI18n[$langCode];
            ?>
            <div id="tab-<?= $langCode ?>"
                class="tab-pane <?php if ($activeTab == $langCode): ?>active<?php endif; ?>"
            >
                <p>
                    <?php
                        $link = Url::toRoute(['main/view', 'id' => $model->id, 'lang' => $langCode], true);
                        if ($model->is_visible && NewsSearchFront::canShow($model, $modelI18n)) {
                            echo Html::a($link, $link, ['target' => '_blank']);
                        } else {
                            echo Yii::t($this->context->tc, 'News invisible at frontend');
                        }
                    ?>
                </p>
                <?php // show as it will show at frontend
                    echo $this->render('../main/view', [
                        'model' => $model,
                        'modelI18n' => $modelI18n,
                    ]);
                ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <br style="clear:both" />
    <hr />

</div>
