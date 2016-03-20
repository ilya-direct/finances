<?php

use yii\db\Migration;

class m160308_201022_init_fin2014_db extends Migration
{
    public function init()
    {
        $this->db='dbFin2014';
        parent::init();
    }

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('balance_check',[
            'id'=> $this->primaryKey(),
            'date'=> $this->date()->notNull()->unique(),
            'consider'=> $this->integer()->notNull(),
            'realmoney'=> $this->integer()->notNull(),
            'diff'=> $this->integer()->notNull(),
        ]);


        $this->createTable('dbx_finance',[
            'id' => $this->primaryKey(),
            'month' => $this->smallInteger()->notNull(),
            'year' => $this->smallInteger()->notNull(),
            'file_name' => $this->string(),
            'modified_time' => $this->dateTime(),
            'download_time' => $this->dateTime(),
            'exists' => $this->boolean()->defaultValue(false)->notNull(),
            'csv_converted' => $this->boolean()->defaultValue(false)->notNull(),
            'in_db' => $this->boolean()->defaultValue(false)->notNull(),
        ]);

        $this->createTable('item',[
            'id'=> $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
        ]);

        $this->createTable('record',[
            'id'=> $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'sum' => $this->integer()->notNull(),
            'itemid' => $this->integer()->notNull(),
            'tcategory' => $this->integer()->notNull(),
        ]);

        $this->createTable('transaction_category',[
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull(),
            'value' => $this->string(50),
            'sign' => $this->string(1),
            'sort' => $this->smallInteger(),
            'deleted' => $this->smallInteger(1)->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey('fk_record_item','record','itemid','item','id');
        $this->addForeignKey('fk_record_tcategory','record','tcategory','transaction_category','id');



    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_record_tcategory','record');
        $this->dropForeignKey('fk_record_item','record');
        $this->dropTable('transaction_category');
        $this->dropTable('record');
        $this->dropTable('item');
        $this->dropTable('dbx_finance');
        $this->dropTable('balance_check');
    }
}
