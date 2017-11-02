<?php

namespace asb\yii2\modules\news_1b_160430\models;

use asb\yii2\modules\news_1b_160430\Module;

use asb\yii2\common_2_170212\base\UniModule;
use asb\yii2\common_2_170212\models\DataModel;

use Yii;
use yii\db\Expression;
use yii\helpers\FileHelper;

/**
 * @property integer $id
 * @property integer $owner_id
 * @property integer $is_visible
 * @property string $image
 * @property string $show_from_time
 * @property string $show_to_time
 * @property string $create_time
 * @property string $update_time
 */
class News extends DataModel //BaseDataModel
{
    //const TABLE_NAME = 'news'; // deprecated

    const AFTERSAVE_LIST = 'list';
    const AFTERSAVE_VIEW = 'view';

    public $defaultOrderBy = ['show_from_time' => SORT_DESC];

    public $title;
    public $body;
    public $unix_show_from_time;
    public $unix_show_to_time;
    public $imagefile;
    public $timezoneshift;
    public $aftersave = self::AFTERSAVE_LIST;

    public $langHelper;
    public $languages;
    public $langCodeMain;

    //public $module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->module)) {
            $this->module = Module::getModuleByClassname(Module::className());
        }

        if (!empty($this->module->params['pageSizeAdmin']) && intval($this->module->params['pageSizeAdmin']) > 0) {
            $this->pageSize = intval($this->module->params['pageSizeAdmin']);
        }

        $this->langHelper = new $this->module->langHelper;
        $param = 'editAllLanguages';
        $editAllLanguages = empty($this->module->params[$param]) ? false : $this->module->params[$param];
        $this->languages = $this->langHelper->activeLanguages($editAllLanguages);//var_dump($this->languages);
        if (empty($this->langCodeMain) ) {
            $this->langCodeMain = $this->langHelper->normalizeLangCode(Yii::$app->language);
        }
    }

/* move to asb\yii2\models\DataModel
    public static function tableName()
    {
        return '{{%' . self::TABLE_NAME . '}}';
    }
*/

    /**
     * @inheritdoc
     */
    public function extraFields()
    {//echo __METHOD__;
        $fields = ['title', 'body'
          //, 'unix_show_from_time', 'unix_show_to_time', 'imagefile', 'timezoneshift'
        ];
        $fields = array_merge($fields, array_keys($this->getRelatedRecords()));//var_dump($fields);exit;
        return array_combine($fields, $fields);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_id'], 'integer'],
            [['is_visible'], 'boolean'],
            [['image'], 'string', 'max' => 255],
            [['imagefile'], 'file'
              , 'extensions' => 'gif, jpg, jpeg, bmp, png'
              , 'checkExtensionByMimeType' => true
              , 'maxSize' => $this->module->params['maxImageSize']
            ],
            [['timezoneshift'], 'integer'],

            [['show_from_time', 'show_to_time', 'create_time', 'update_time'], 'safe'],
            //??[['show_from_time', 'show_to_time'], 'date', 'format' => 'php:Y-m-d H:i'],
            //??[['show_from_time', 'show_to_time'], 'date', 'format' => 'php:Y-m-d'],

            //[['title'], 'string'], // not need here - in i18n-model
            ['aftersave', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t($this->tcModule, 'ID'),
            'owner_id' => Yii::t($this->tcModule, 'Owner ID'),
            'is_visible' => Yii::t($this->tcModule, 'Is visible'),
            //'image' => Yii::t($this->tcModule, 'Image'),
            'imagefile' => Yii::t($this->tcModule, 'Image'),
            'show_from_time' => Yii::t($this->tcModule, 'Show from time'),
            'show_to_time' => Yii::t($this->tcModule, 'Show to time'),
            'create_time' => Yii::t($this->tcModule, 'Create time'),
            'update_time' => Yii::t($this->tcModule, 'Update time'),
        ];
    }

    /**
     * @inheritdoc
     * @return NewsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NewsQuery(get_called_class());
    }

    /**
     * Data correction immediatly before save.
     * Datetime fields will convert into Expression-class.
     */
    protected function correctSavedData($insert)
    {
        if ($insert) {

            $tzShiftSec = intval(date('Z')); // server time zone shift in seconds: west UTC <0, east UTC >0
            if (!empty($tzShiftSec)) {//var_dump($tzShiftSec);exit;
                $now = new Expression("DATE_SUB(NOW(), INTERVAL {$tzShiftSec} SECOND)"); // UTC time
            } else {
                $now = new Expression('NOW()'); // server time
            }
            $this->create_time = $now;
            if (empty($this->show_from_time)) $this->show_from_time = $now;
            $this->owner_id = Yii::$app->user->identity->id;
        } else {
/*
            // this corrections move to controller to POST preprocessing
            if (!empty($this->timezoneshift)) {//var_dump($this->attributes);var_dump($this->timezoneshift);
                $tz = intval($this->timezoneshift);
                $this->show_from_time = new Expression("DATE_ADD('{$this->show_from_time}', INTERVAL {$tz} MINUTE)");
                $this->show_to_time   = new Expression("DATE_ADD('{$this->show_to_time}',   INTERVAL {$tz} MINUTE)");
            }//var_dump($this->attributes);exit;
*/
        }
    }

    /**
     * Prepare for i18n-models load data from arrays $this->title and $this->body
     */
    protected function prepareI18nData($langCode)
    {
        $data = [];
        //var_dump($this->title);var_dump($this->body);
        if (is_array($this->title) && is_array($this->body)) {
            $data = [
                'title' => empty($this->title[$langCode]) ? '' : $this->title[$langCode],
                'body'  => empty($this->body[$langCode])  ? '' : $this->body[$langCode],
                'news_id' => $this->id,
                'lang_code' => $langCode,
            ];
        }//var_dump($data);
        return $data;
    }

    protected $savedFields = [];
    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {//echo __METHOD__;var_dump($this->attributes);exit;

        if ($runValidation && !$this->validate($attributeNames)) {
            return false;
        }

        // to prevent validation datetime fields that converted into Expression-class
        $savedFields = [ // save some fields before transformation
            'create_time'    => $this->create_time,
            'show_from_time' => $this->show_from_time,
            'show_to_time'   => $this->show_to_time,
        ];
        $this->correctSavedData($this->getIsNewRecord());

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = parent::save(false, $attributeNames);//var_dump($result);
            if ($result !== false) {
                $modelsI18n = $this::prepareI18nModels($this);
                foreach ($modelsI18n as $langCode => $modelI18n) {
                    $data = $this->prepareI18nData($langCode);
                    if (!empty($data)) {
                        $modelI18n->load($data, '');
                        $result = $modelI18n->save();//var_dump($result);
                        if ($result === false) break;
                    }
                }
            }
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        // restore transformed fields
        foreach($savedFields as $name => $value) {
            $this->$name = $value;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        //$this->correctSavedData($insert); // not here - immediately before saving
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
/*
        $pageSize = intval($this->module->params['pageSizeAdmin']);
        if ($pageSize > 0) {
            $this->pageSize = $pageSize;
            $this->page = $this->calcPage();//echo __METHOD__.": calcPage(id={$this->id},pageSize={$pageSize})={$this->page}<br>";exit;
        }
*/
        $this->page = $this->calcPage();//echo __METHOD__.": calcPage(id={$this->id},pageSize={$this->pageSize})={$this->page}<br>";exit;
    }

    /**
     * @param integer $modelId main model ID
     * @return string relative subdir without main upload dir prefix
     */
    public static function getImageSubdir($modelId)
    {
        //$subdir = $modelId; // if too many records subdir will be more complicated
        $subdir = floor($modelId / 1000) . '/' . $modelId;
        return $subdir;
    }

    /**
     * Declares a `has-many` relation.
     */
    public function getI18n()
    {
        //return $this->hasMany(NewsI18n::className(), ['news_id' => 'id']);
        return $this->hasMany($this->module->model('NewsI18n')->className(), ['news_id' => 'id']);
    }

    /**
     * Prepare multilang models array.
     * @return array of NewsI18n
     */
    public static function prepareI18nModels($model)
    {//echo __METHOD__;var_dump($model->attributes);
        //$languages = LangHelper::activeLanguages();//var_dump($languages);
        //$config = Module::getModuleConfigByClassname(Module::className());//var_dump($config);
        //$langHelper = $config['params']['langHelper'];

        $moduleClass = static::moduleClass();//var_dump($module);exit;
        if (!$moduleClass) $moduleClass = Module::className(); //?? or better throw new \Exception("Can't found module for " . static::className());
        $module = UniModule::getModuleByClassname($moduleClass);

        $langHelper = $module->langHelper;
        //$languages = $langHelper::activeLanguages();//var_dump($languages);
        $editAllLanguages = empty($module->params['editAllLanguages']) ? false : $module->params['editAllLanguages'];
        $languages = $langHelper::activeLanguages($editAllLanguages);//var_dump($languages);exit;

        $mI18n = $model->i18n;//var_dump($mI18n);exit;

        //$conf = Module::getModuleConfigByClassname(Module::className());//var_dump($conf);
        //$contentHelperClass = $conf['params']['contentHelper'];
        //$module = Module::getModuleByClassname(Module::className());
        //$contentHelperClass = $module->contentHelper;
        //$contentHelper = new $contentHelperClass;

        $modelsI18n = [];
        foreach ($mI18n as $modelI18n) {//var_dump($modelI18n->attributes);
            //$modelI18n->body = $contentHelper::afterSelectBody($modelI18n->body); //!! move to self::correctI18nBodies()
            $modelsI18n[$modelI18n->lang_code] = $modelI18n;
        }
        $modelsI18n = static::correctI18nBodies($modelsI18n);//var_dump($modelsI18n);exit;

        foreach ($languages as $langCode => $lang) {
            if (empty($modelsI18n[$langCode])) {
                //$modelsI18n[$langCode] = (new NewsI18n())->loadDefaultValues();
                $newNewsI18n = $module::model('NewsI18n');//var_dump($newNewsI18n);exit;
                $modelsI18n[$langCode] = $newNewsI18n->loadDefaultValues();
                $modelsI18n[$langCode]->lang_code = $langCode;
            }
        }//var_dump($modelsI18n);exit;
        return $modelsI18n;
    }

    /**
     * Use ContentHelper to correct texts after visual editor.
     * Need as independent method to repeat run after unsuccessful validation.
     */
    public static function correctI18nBodies($modelsI18n)
    {
        $module = Module::getModuleByClassname(Module::className());
        $contentHelperClass = $module->contentHelper;

        foreach ($modelsI18n as $modelI18n) {//var_dump($modelI18n->attributes);exit;
            $modelI18n->body = $contentHelperClass::afterSelectBody($modelI18n->body);
        }
        return $modelsI18n;
    }

    /**
     * @inheritdoc
     * Delete also i18n-records from joined table
     */
    public function delete()
    {
        $id = $this->id;
        $transaction = static::getDb()->beginTransaction();
        try {
            $modelsI18n = $this->i18n;//var_dump($modelsI18n);exit;
            $result = true;
            foreach ($modelsI18n as $modelI18n) {
                $result = $modelI18n->deleteInternal();
                if ($result === false) break;
            }
            if ($result !== false) {
                $result = $this->deleteInternal();
            }
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
                $this->deleteFiles($id);
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    /**
     * Delete uploaded files connected with the model.
     * @param integet $id model id
     */
    protected function deleteFiles($id)
    {//echo __METHOD__."($id)";
        $subdir = static::getImageSubdir($id);
        if (!empty($this->module->params['uploadsNewsDir'])) {
            $uploadsDir = Yii::getAlias($this->module->params['uploadsNewsDir']) . '/' . $subdir;//var_dump($uploadsDir);
            //@FileHelper::removeDirectory($uploadsDir);
            rename($uploadsDir, $uploadsDir . '~remove~' . date('ymd~His') . '~');
        }
        if (array_key_exists('@webfilespath', Yii::$aliases) && !empty($this->module->params['filesSubpath'])) {
            $webfilesDir = Yii::getAlias('@webfilespath') . '/' . $this->module->params['filesSubpath'] . '/' . $subdir;//var_dump($webfilesDir);
            @FileHelper::removeDirectory($webfilesDir);
        }
        //?! Yii2-advanced temblate has 2 web roots. Here delete only one.
    }

    /**
     * @inheritdoc
     * Save attributes $this->title[LANG] or $this->body[LANG] if exists
     * @param string $name the unsafe attribute name
     * @param mixed $value the attribute value
     */
    public function onUnsafeAttribute($name, $value)
    {//echo __METHOD__;var_dump($name);var_dump($value);exit;
        if (in_array($name, ['title', 'body'])) {
            if (empty($this->$name)) $this->$name = [];
            foreach ($value as $lang => $text) {
                if ($this->langHelper->isValidLangCode($lang)) {
                    $lang = $this->langHelper->normalizeLangCode($lang);
//??                $this->$name[$lang] = $text; //??bug - illegal offset
//??                ($this->$name)[$lang] = $text; //??bug - unexpected '['
                    if ($name == 'title') $this->title[$lang] = $text;
                    if ($name == 'body')  $this->body[$lang] = $text;
                }
            }//var_dump($this->$name);
        }
    }

}
