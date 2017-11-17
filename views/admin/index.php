<?php

    /* @var $searchModel asb\yii2\modules\news_1b_160430\models\NewsSearch */
    /* @var $dataProvider yii\data\ActiveDataProvider */
    /* @var $currentId integer current item id */
    /* @var $this yii\web\View */

    //use asb\yii2cms\modules\sys\modules\user\Module as UserModule;
    use asb\yii2\modules\news_1b_160430\models\Formatter;

    //use asb\yii2\modules\news_1b_160430\assets\AdminAsset;
    use asb\yii2\common_2_170212\assets\BootstrapCssAsset;
    use asb\yii2\common_2_170212\assets\CommonAsset;

    //use asb\yii2cms\widgets\grid\ButtonedActionColumn;
    use asb\yii2\common_2_170212\widgets\grid\ButtonedActionColumn;

    use asb\yii2\common_2_170212\widgets\Alert;

    use kartik\date\DatePicker;

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\grid\GridView;

    $heightImage = 35; //px
    $gridViewId = 'news-grid';
    $gridHtmlClass = 'news-list-grid';

    BootstrapCssAsset::register($this); // need to move up bootstrap.css
    $assetsSys = CommonAsset::register($this);
    //$assets = AdminAsset::register($this);
    $assets = $this->context->module->registerAsset('AdminAsset', $this);//var_dump($assets);

    $this->title = Yii::t($this->context->tcModule, 'News');
    if (!empty(Yii::$app->params['adminPath'])) {
        $this->params['breadcrumbs'][] = [
            'label' => Yii::t($this->context->tcModule, 'Admin startpage'),
            'url' => ['/' . Yii::$app->params['adminPath']],
        ];
    }
    //$this->params['breadcrumbs'][] = $this->title;
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($this->title),
        'url' => Url::to(['index']),
    ];

    //$userModel = UserModule::model('User');
    $userModel = $this->context->module->userIdentity;

    $formName = basename($searchModel::className());
    $paramSearch = Yii::$app->request->get($formName, []);
    foreach ($paramSearch as $key => $val) {
        if (empty($val)) unset($paramSearch[$key]);
    }
    $paramSort = Yii::$app->request->get('sort', '');//var_dump($paramSort);
    $pager = $dataProvider->getPagination();
    $this->params['buttonOptions'] = ['data' => ['search' => $paramSearch, 'sort' => $paramSort, 'page' => $pager->page + 1]];

?>
<div class="news-index">

    <h1><a href="<?= Url::to(['index']) ?>"><?= Html::encode($this->title) ?></a></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= Alert::widget(); ?>

    <p>
        <?php if(Yii::$app->user->can('roleNewsAuthor')): ?>
            <?= Html::a(Yii::t($this->context->tcModule, 'Create News'), ['create'], ['class' => 'btn btn-success']) ?>
        <?php elseif(Yii::$app->user->can('roleNewsModerator')): ?>
            <?= Yii::t($this->context->tcModule, "Moderator can't create news") ?>
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'id' => $gridViewId,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => $gridHtmlClass],
        'formatter' => ['class' => Formatter::className(),
            'timeZone' => 'UTC'
        ],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => 'No',
                'headerOptions' => ['class' => 'align-center'],
                'contentOptions' => ['class' => 'align-right'],
            ],
            [
                'attribute' => 'image',
                'header' => false,
                'filter' => false,
                'contentOptions' => [
                    'style' => 'padding: 0px',
                    'class' => 'align-center'
                    //'class' => 'no-padding', //dont work
                ],
                'content' => function ($model, $key, $index, $column) use($heightImage) {
                    if( !empty($model->image)) {
                        return Html::img($this->context->uploadsNewsUrl . '/' . $model->image, [
                            'height' => $heightImage,
                        ]);
                    }
                }
            ],
            [
                'attribute' => 'title',
                'header' => Yii::t($this->context->tcModule, 'Title'),
                'filter' => Html::activeTextInput($searchModel, 'title', [//'filterInputOptions'
                    'id' => 'search-title',
                    'class' => 'form-control'
                ]),
            ],
            [
                'attribute' => 'owner_id',
                'label' => Yii::t($this->context->tcModule, 'Author'),
                'format' => 'username',
                'filter' => (
                    Yii::$app->user->can('roleNewsModerator')
                        ? $userModel::usersNames()
                        : false
                ),
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => '-' . Yii::t($this->context->tcModule, 'all') . '-'],
            ],
            [
                'attribute' => 'show_from_time',
                'label' => Yii::t($this->context->tcModule, 'Show from time (UTC)'),
                'format' => 'datetime',
                'options' => [
                    'id' => 'show_from_time',
                    'style' => 'width:240px',
                ],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute'  => 'show_from_time_begin',
                    'attribute2' => 'show_from_time_end',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => ['format' => 'yyyy-mm-dd'],
                ]),
            ],
            [
                'attribute' => 'show_to_time',
                'label' => Yii::t($this->context->tcModule, 'Show to time (UTC)'),
                'format' => 'datetime',
                'options' => [
                    'id' => 'show_to_time',
                    'style' => 'width:240px',
                ],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute'  => 'show_to_time_begin',
                    'attribute2' => 'show_to_time_end',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => ['format' => 'yyyy-mm-dd'],
                ]),
            ],
            [
                'attribute' => 'is_visible',
                'label' => Yii::t($this->context->tcModule, 'Show'),
                'format' => 'boolean',
                'filter' => [
                    true  => Yii::t('yii', 'Yes'),
                    false => Yii::t('yii', 'No'),
                ],
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => '-' . Yii::t($this->context->tcModule, 'all') . '-'],
                'options' => [
                    'style' => 'width:85px', //'class' => 'width-min',
                ],
            ],
            [
                //'label' => Yii::t($this->context->tcModule, 'ID'),
                'attribute' => 'id',
                'format' => 'text',
                'headerOptions' => ['class' => 'align-center'],
                'contentOptions' => ['class' => 'align-right'],
                'options' => ['style' => 'width:50px'],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'style' => 'padding:5px',
                    //'maxlength' => 6,
                ],
            ],
            [
              //'class' => 'yii\grid\ActionColumn',
                'class' => ButtonedActionColumn::className(),
                'header' => Yii::t($this->context->tcModule, 'Actions'),
                'buttonSearch' => Html::submitInput(Yii::t($this->context->tcModule, 'Find'), [
                    'id' => 'search-button', 'class' => 'btn',
                ]),
                'buttonClear' => Html::buttonInput('C', [
                    'id' => 'search-clean',
                    'class' => 'btn btn-danger',
                    'title' => Yii::t($this->context->tcModule, 'Clean search fields'),
                ]),
              //'buttonOptions' => $this->params['buttonOptions'],
                'headerOptions' => ['class' => 'align-center'],
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                //'template' => '{change-visible} {update} {delete}',
                'template' => '{change-visible} {view} {update} {delete}',
                'buttons' => [
                    'change-visible' => function($url, $model, $key) use($pager, $formName) {
                        //$icon = $model->is_visible ? 'minus' : 'plus';
                        $icon = $model->is_visible ? 'ok' : 'minus';
                        $title = $model->is_visible ? Yii::t($this->context->tcModule, 'Hide')
                                                    : Yii::t($this->context->tcModule, 'Show');
                        $options = array_merge([
                            'title' => $title,
                            'aria-label' => $title,
                            'data-pjax' => '0',
                            'data-method' => 'post',
                            'data-confirm' => Yii::t($this->context->tcModule, 'Are you sure to change visibility of this article?'),
                        ], $this->params['buttonOptions']);
                        $url = Url::to(['change-visible'
                          , 'id' => $model->id
                          , 'sort' => $this->params['buttonOptions']['data']['sort']
                          , $formName => $this->params['buttonOptions']['data']['search']
                          , 'page' => $pager->page + 1
                        ]);//var_dump($url);
                        return Html::a("<span class='glyphicon glyphicon-{$icon}'></span>", $url, $options);
                    },
                    'delete' => function($url, $model, $key) use($pager, $formName) {
                        $options = [
                            'title' => Yii::t('yii', 'Delete'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                            'data-confirm' => Yii::t($this->context->tcModule
                                                , 'Are you sure you want to delete this item with ID={id}?', ['id' => $key]), //+id
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ];
                        //$options = array_merge($options, $this->params['buttonOptions']);

                        // add to url sort criteria and page number - to return after deletion to same page
                        $params = is_array($key) ? $key : ['id' => (string) $key];
                        $params['page'] = $this->params['buttonOptions']['data']['page'];
                        $params['sort'] = $this->params['buttonOptions']['data']['sort'];
                        $params[$formName] = $this->params['buttonOptions']['data']['search'];
                        $params[0] = 'delete';//var_dump($params);
                        $url = Url::toRoute($params);//var_dump($url);

                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                    },
                ],
            ],
        ],
    ]); ?>

</div>

<?php
    $this->registerJs("
        jQuery('.{$gridHtmlClass} table tr').each(function(index) {
            var elem = jQuery(this);
            var id = elem.attr('data-key');
            if (id == '{$currentId}') {
               elem.addClass('bg-success'); //?? overwrite by .table-striped > tbody > tr:nth-of-type(2n+1)
               elem.css({'background-color': '#DFD'}); // work always
            }
        });
    ");
?>
