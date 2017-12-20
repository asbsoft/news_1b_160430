<?php
    /* @var $model asb\yii2\modules\news_1b_160430\models\News */
    /* @var $modelsI18n array of asb\yii2\modules\news_1b_160430\models\NewsI18n */
    /* @var $this yii\web\View */
    /* @var $form yii\widgets\ActiveForm */
    /* @var $activeTab string */

    use asb\yii2\common_2_170212\widgets\ckeditor\CkEditorWidget;

    //use asb\yii2\modules\news_1b_160430\assets\AdminAsset;
    use asb\yii2\common_2_170212\assets\CommonAsset;
    use asb\yii2\common_2_170212\assets\FlagAsset;

    use asb\yii2\common_2_170212\widgets\Alert;

    use yii\jui\DatePicker as JuiDatePicker;

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ActiveForm;

    // defaults
    if (empty($heightImage)) $heightImage = 100; //px
    if (empty($heightEditor)) $heightEditor = 300; //px
    if (empty($rowsCountTextarea)) $rowsCountTextarea = 8; //lines // for debug
    if (empty($dateFormat)) $dateFormat = 'php:Y-m-d H:i';

    if ($activeTab === true) $activeTab = $this->context->langCodeMain; // select tab pane with error

    $assetsSys = CommonAsset::register($this);
    $assetsFlag = FlagAsset::register($this);
    //$assets = AdminAsset::register($this); // to work inheritance use:
    $assets = $this->context->module->registerAsset('AdminAsset', $this);

    $langHelper = $this->context->module->langHelper;
    $editAllLanguages = empty($this->context->module->params['editAllLanguages'])
                      ? false : $this->context->module->params['editAllLanguages'];
    $languages = $langHelper::activeLanguages($editAllLanguages);

    $enableEditVisibility = (!Yii::$app->user->can('roleNewsModerator') && Yii::$app->user->can('roleNewsAuthor')) ? false : true;//var_dump($enableEditVisibility);

    $author = Yii::$app->user->identity->username;
    if (!empty($model->owner_id)) {
        $user = Yii::$app->user->identity->findIdentity($model->owner_id);
        if (!empty($user)) $author = $user->username;
    }

    $editorOptions = [
        'height' => $heightEditor,
        'language' => substr(Yii::$app->language, 0, 2),
      
        'preset' => 'full',     // full editor
      //'preset' => 'standard', // middle
      //'preset' => 'basic',    // minimal editor

      //'inline' => false, // default
        'filter' => 'image',
    ];

    if (empty($model->id)) { // news not create yet - can't load images
        $managerOptions = false;
    } else {
        $elfController = [$this->context->module->uniqueId . '/el-finder', 'id' => $model->id];
        $managerOptions = [
            'controller' => $elfController,
            'rootPath' => $this->context->module->params['uploadsNewsDir'] . '/' . $model::getImageSubdir($model->id),
            'filter' => 'image',
        ];
    }

?>
<div class="news-form">

    <?= Alert::widget(); ?>

    <table><tr>
        <td><?= Yii::t($this->context->tcModule, 'Author') ?>: <i><?= $author ?></i>&nbsp;</td>
      <?php if (!empty($model->id)): ?>
        <td>&nbsp;&nbsp;&nbsp;<?= Yii::t($this->context->tc, 'Record time (create/update)') ?>:&nbsp;</td>
        <td class="min-width"><?= $model->create_time?></td>
        <td>&nbsp;/&nbsp;</td>
        <td class="min-width"><?= $model->update_time?></td>
      <?php endif; ?>
    </tr></table>

    <?php $form = ActiveForm::begin([
              'options' => ['enctype' => 'multipart/form-data'],
              'enableClientValidation' => false, // disable JS-validation 
              'enableClientScript' => false, // form will not generate any JavaScript
    ]) ?>
    <?= $form->field($model, 'timezoneshift', ['inputOptions' => ['id' => 'tz']])->hiddenInput()->label(false) ?>

    <table>
    <tr class="valign-top">
        <td class="nowrap">
            <?php
                if (empty($model->image)) {
                    echo Html::img("{$assetsSys->baseUrl}/img/no-image.jpg", [
                        'class' => 'border-black-1px', //'style' => "border: solid 1px black",
                        'height' => $heightImage,
                        'width' => $heightImage,
                        'title' => Yii::t($this->context->tcModule, 'No image yet'),
                    ]);
                } else {
                    echo Html::img($this->context->uploadsNewsUrl . '/' . $model->image, [
                        'class' => 'border-black-1px',
                        'height' => $heightImage,
                    ]);
                }
             ?>
        </td>
        <td>&nbsp;&nbsp;</td>
        <td>
            <?= $form->field($model, 'imagefile')->fileInput([
                    'class' => 'form-control',
            ]) ?>
        </td>
        <td>&nbsp;</td>
        <td class="nowrap">
          <?php if ($enableEditVisibility): ?>
            <?= $form->field($model, 'is_visible')->checkbox([]) ?>
          <?php else: ?>
            &nbsp;
          <?php endif; ?>
        </td>
        <td>&nbsp;&nbsp;</td>
        <td>
          <table>
          <tr class="valign-top">
            <?php $field = 'show_from_time';
                  //echo $form->field($model, $field)->textInput();
            ?>
              <td class="width-min nowrap">
                  <?= Html::label(Yii::t($this->context->tcModule, 'Show from time'), $field); ?>
              </td>
              <td>&nbsp;</td>
              <td>
                  <?= $form->field($model, $field)->widget(JuiDatePicker::className(), [
                          'language' => substr(Yii::$app->language, 0, 2),
                          'options' => [
                              'id' => $field,
                              'class' => 'form-control',
                          ],
                          'dateFormat' => $dateFormat,
                  ])->label(false);/**/ ?>
              </td>
              <td>&nbsp;</td>
            <?php $field = 'show_to_time';
                  //echo $form->field($model, $field)->textInput();
            ?>
              <td class="width-min nowrap">
                  <?= Html::label(Yii::t($this->context->tcModule, 'Show to time'), $field); ?>
              </td>
              <td>&nbsp;</td>
              <td>
                  <?= $form->field($model, $field)->widget(JuiDatePicker::className(), [
                          'language' => substr(Yii::$app->language, 0, 2),
                          'options' => [
                              'id' => $field,
                              'class' => 'form-control',
                          ],
                          'dateFormat' => $dateFormat,
                  ])->label(false);/**/ ?>
                  <?= ''/*KvDatePicker::widget(['type' => KvDatePicker::TYPE_COMPONENT_APPEND,
                      'model' => $model,
                      'attribute'  => $field,
                      'options' => ['id' => $field],
                      'pluginOptions' => ['format' => 'yyyy-mm-dd'],
                  ]);/**/ ?>
              </td>
          </tr>
          <tr>
              <td colspan="7">
                  <sup>*</sup>
                  <small><?= Yii::t($this->context->tcModule,
                      "Input here locat time of your time zone. It will be automatically convert into common UTC time format before save on server."
                  ) ?></small>
              </td>
          </tr>
          </table>
        </td>
    </tr>
    </table>

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

        <?php $this->startBlock('tab-content') ?>
        <div class="tab-content">
            <?php // multi-lang part - content
              foreach ($languages as $langCode => $lang):
                  $countryCode2 = strtolower(substr($langCode, 3, 2));
                  $flag = '<span class="flag f16"><span class="flag ' . $countryCode2 . '" title="' . $lang->name_orig . '"></span></span>';
                  $labels = $modelsI18n[$langCode]->attributeLabels();
                  //var_dump($modelsI18n[$langCode]->attributes);
            ?>
            <div id="tab-<?= $langCode ?>"
                class="tab-pane <?php if ($activeTab == $langCode): ?>active<?php endif; ?>"
            >
                <?= $form->field($modelsI18n[$langCode], "[{$langCode}]title",[
                        'options' => [
                            'class'=>'news-title',
                        ],
                    ])->label($flag . ' ' . $labels['title'])
                      ->textInput() ?>

                <?= $form->field($modelsI18n[$langCode], "[{$langCode}]body")
                    ->label(false)
                  //->label($flag . ' ' . $labels['body'])
                  //->textarea(['rows' => $rowsCountTextarea]) // for debug
                    ->widget(CkEditorWidget::className(), [
                        'id' => "editor-{$langCode}",
                        //'inputOptions' => ['value' => $modelsI18n[$langCode]->body],
                        'editorOptions' => $editorOptions,
                        'managerOptions' => $managerOptions,
                    ])
                ?>

                <?php //echo 'init:' . mb_strlen($modelsI18n[$langCode]->body, 'UTF-8') . '-utf8/bytes:' . strlen($modelsI18n[$langCode]->body); ?>
            
            </div>
            <?php endforeach; ?>
        </div>
        <?php $this->stopBlock('tab-content') ?>
    </div>

    <?php if (!empty($model->isNewRecord)): ?>
        <div><small><?= Yii::t($this->context->tcModule,
           "When create new record you can't upload images in text editor. You can do this in update mode"
        ) ?></small></div>
        <br />
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord
            ? Yii::t($this->context->tc, 'Create') //?? no such standard message: Yii::t('yii', 'Create')
            : Yii::t('yii', 'Update')
            , [ 'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
        <?= Html::submitButton(Yii::t($this->context->tc, 'Save & view'), [
               'id' => 'save-and-view',
               'class' => 'btn',
            ]) ?>
        <?= $form->field($model, 'aftersave', [
                'inputOptions' => ['id' => 'aftersave'],
            ])->hiddenInput()->label(false) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
    // set client's time zone
    $this->registerJs("
        now = new Date()
        jQuery('#tz').val(now.getTimezoneOffset());
    ");

    $aftersave_view = $model::AFTERSAVE_VIEW;
    $this->registerJs("
        jQuery('#save-and-view').bind('click', function() {
            jQuery('#aftersave').val('{$aftersave_view}');
        });
    ");
?>
