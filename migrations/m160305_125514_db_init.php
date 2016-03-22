<?php

use yii\db\Migration;

class m160305_125514_db_init extends Migration
{
    public function init()
    {
        $this->db='dbFin2016';
        parent::init();
    }

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('item',[
            'id'=> $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
        ]);

        $this->createTable('note',[
            'id'=> $this->primaryKey(),
            'date' => $this->date()->notNull()->unique(),
            'text' => $this->text()->notNull(),
        ]);

        $this->createTable('column_type',[
            'id'=> $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
        ]);

        $this->createTable('column',[
            'id'=> $this->primaryKey(),
            'column_type_id'=> $this->smallInteger()->notNull(),
            'letter'=> $this->string(2),
            'sign'=> $this->string(1),
            'name' => $this->string(50)->notNull(),
            'value' => $this->string(50)->notNull()->unique(),
            'zero_sum' => $this->boolean()->notNull()->defaultValue(false),
            'deleted' => $this->smallInteger(1)->notNull()->defaultValue(0),
        ]);

        $this->createTable('record',[
            'id'=> $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'sum' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'column_id' => $this->integer()->notNull(),
        ]);

        $this->createTable('balance_check',[
            'id'=> $this->primaryKey(),
            'date'=> $this->date()->notNull()->unique(),
            'consider'=> $this->integer()->notNull(),
            'real'=> $this->integer()->notNull(),
            'difference'=> $this->integer()->notNull(),
        ]);

        $this->createTable('dbx_finance',[
            'id' => $this->primaryKey(),
            'month' => $this->smallInteger()->notNull(),
            'year' => $this->smallInteger()->notNull(),
            'modified_time' => $this->dateTime(),
            'downloaded_time' => $this->dateTime(),
            'downloaded' => $this->boolean()->defaultValue(false)->notNull(),
            'csv_converted' => $this->boolean()->defaultValue(false)->notNull(),
            'in_db' => $this->boolean()->defaultValue(false)->notNull(),
            'file_name' => $this->string(),
        ]);


        $this->addForeignKey('fk_record_item','record','item_id','item','id');
        $this->addForeignKey('fk_record_column','record','column_id','column','id');
        $this->addForeignKey('fk_column_type','column','column_type_id','column_type','id');

        $this->batchInsert('column_type',['id','name'],[
            [1, 'Single'],
            [2, 'Multiple'],
            [3, 'Note'],
            [4, 'Checkpoint'],
            [5, 'Date'],
            [6, 'Correcting'],
        ]);

        $this->insert('balance_check',['date'=>'2013-12-31','consider'=>15114,'real'=>15114,'difference'=>0]);
        //$this->insert('balance_check',['date'=>'2015-12-31','consider'=>43996,'real'=>44009,'difference'=>13]);

        $this->batchInsert('column',['value','name','column_type_id','letter','sign','deleted'],[
            ['Дата','date',5,'D',null,0],
            ['Мама','p_mom_multiple',2,'F','+',0],
            ['Мама (PM)','p_mompm',1, null ,'+',1],
            ['PM','p_mompm',1,'H','+',0],
            ['Ученики','p_pupils',1, null ,'+',0],
            ['Другие доходы','p_other_multiple',2,'I','+',0],
            ['Универ','m_university',1,null,'-',1],
            ['MTI','m_mti',1,null,'-',0],
            ['МГТУ','m_university',1,'L','-',0],
            ['бенз','m_petrol',1,'M','-',0],
            ['Мобила','m_mobile',1, null ,'-',1],
            ['Моб','m_mobile',1,'N','-',0],
            ['iPad','m_ipad',1,null,'-',0],
            ['Развлечения','m_spend_multiple',2,'O','-',0],
            ['Гулянки','m_spend_multiple',2,null,'-',1],
            ['Другие расходы','m_other_multiple',2,'Q','-',0],
            ['Заметки','note',3,'T',null,0],
            ['ТС по деньгам','realmoney',4,'X', null,0],
            ['Корректировка','correcting',6,'Y', null,0],
        ]);



    }
    
    public function safeDown()
    {
        $this->dropForeignKey('fk_column_type','column');
        $this->dropForeignKey('fk_record_column','record');
        $this->dropForeignKey('fk_record_item','record');

        $this->dropTable('dbx_finance');
        $this->dropTable('balance_check');
        $this->dropTable('record');
        $this->dropTable('column');
        $this->dropTable('column_type');
        $this->dropTable('note');
        $this->dropTable('item');
    }

}
