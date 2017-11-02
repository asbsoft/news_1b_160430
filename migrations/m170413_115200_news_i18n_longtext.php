<?php

use asb\yii2\modules\news_1b_160430\models\NewsI18n;

use yii\db\Migration;

class m170413_115200_news_i18n_longtext extends Migration
{
    protected $tableName;

    public function init()
    {
        parent::init();

        $this->tableName = $this->db->schema->getRawTableName(NewsI18n::tableName());
    }

    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $sql = "ALTER TABLE `{$this->tableName}` CHANGE `body` `body` LONGTEXT";
            $this->execute($sql);
        }
    }

    public function safeDown()
    {
        echo basename(__FILE__, '.php') . " cannot be reverted.\n";
        return false;
    }
}
