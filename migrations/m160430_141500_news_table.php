<?php

use asb\yii2\modules\news_1b_160430\Module;
use asb\yii2\modules\news_1b_160430\models\News;

use yii\db\Schema;
use yii\db\Migration;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class m160430_141500_news_table extends Migration
{
    protected $tableName;
    protected $idxNamePrefix;

    public function init()
    {
        parent::init();

        $this->tableName     = News::tableName();

        //$this->idxNamePrefix = 'idx-' . News::TABLE_NAME; // deprecated
        $this->idxNamePrefix = 'idx-' . News::baseTableName();
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
            'owner_id' => $this->integer(),
            'is_visible' => $this->boolean()->notNull()->defaultValue(false),
            'image' => $this->string(255),
            'show_from_time' => $this->datetime()->notNull(),
            'show_to_time' => $this->datetime(),
            'create_time' => $this->datetime()->notNull(),
            'update_time' => $this->timestamp(),
        ], $tableOptions);
        $this->createIndex("{$this->idxNamePrefix}-owner-id",  $this->tableName, 'owner_id');
        $this->createIndex("{$this->idxNamePrefix}-visible",   $this->tableName, 'is_visible');
        $this->createIndex("{$this->idxNamePrefix}-show-from", $this->tableName, 'show_from_time');
        $this->createIndex("{$this->idxNamePrefix}-show-to",   $this->tableName, 'show_to_time');
    }

    public function safeDown()
    {
        //echo basename(__FILE__, '.php') . " cannot be reverted.\n";
        //return false;

        $this->dropTable($this->tableName);
    }

}
