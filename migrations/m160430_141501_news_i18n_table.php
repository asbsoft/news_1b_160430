<?php

use asb\yii2\modules\news_1b_160430\Module;
use asb\yii2\modules\news_1b_160430\models\NewsI18n;

use yii\db\Schema;
use yii\db\Migration;
use yii\db\Expression;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class m160430_141501_news_i18n_table extends Migration
{
    protected $tableName;
    protected $idxNamePrefix;

    public function init()
    {
        parent::init();

        // if problems with autoload (classes not found):
        //Yii::setAlias('@asb/yii2', dirname(dirname(dirname(__DIR__))) . '/yii2-common_2_170212');
        //Yii::setAlias('@asb/yii2/modules/news_1b_160430', dirname(__DIR__));//var_dump(Yii::$aliases);exit;

        $this->tableName     = NewsI18n::tableName();

        //$this->idxNamePrefix = 'idx-' . NewsI18n::TABLE_NAME; // deprecated
        $this->idxNamePrefix = 'idx-' . NewsI18n::baseTableName();
    }
    
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'news_id' => $this->integer()->notNull(),
            'lang_code' => $this->string(5)->notNull(),
            'title' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull()->defaultValue(''),
        ], $tableOptions);
        $this->createIndex("{$this->idxNamePrefix}-news-id",  $this->tableName, 'news_id');
    }

    public function safeDown()
    {
        //echo basename(__FILE__, '.php') . " cannot be reverted.\n";
        //return false;

        $this->dropTable($this->tableName);
    }

}
