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

        $this->createTable('xlsx_column',[
            'id'=> $this->primaryKey(),
            'type'=> $this->smallInteger()->notNull(),
            /*
             * 1 - single
             * 2 - multiple
             * 3 - Note
             * 4 - Checkpoint
             * 5 - Date
             *
             */
            'letter'=> $this->string(2)->notNull(),
            'sign'=> $this->smallInteger(1),
            'name' => $this->string(50)->notNull()->unique(),
            'value' => $this->string(50),
            'zero_sum' => $this->boolean()->notNull()->defaultValue(false),
            'deleted' => $this->smallInteger(1)->notNull()->defaultValue(0),
        ]);

        $this->createTable('record',[
            'id'=> $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'sum' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'xlsx_column_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_record_item','record','item_id','item','id');
        $this->addForeignKey('fk_record_xlsx_column','record','xlsx_column_id','xlsx_column','id');


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
            'in_db' => $this->boolean()->defaultValue(false)->notNull(),
            'file_name' => $this->string(),
        ]);


        $this->insert('balance_check',['date'=>'2015-12-31','consider'=>43996,'real'=>44009,'difference'=>13]);

        $this->batchInsert('xlsx_column',['value','name','type','letter','sign','zero_sum'],[
            ['Дата','date',5,'D',NULL,0],
            ['Мама','p_mom_multiple',2,'F',1,0],
            ['PM','p_mompm',1,'H',1,0],
            ['Другие доходы','p_other_multiple',2,'I',1,0],
            ['МГТУ','m_university',1,'L',-1,0],
            ['бенз','m_petrol',1,'M',-1,0],
            ['Моб','m_mobile',1,'N',-1,0],
            ['Развлечения','m_spend_multiple',2,'O',-1,0],
            ['Другие расходы','m_other_multiple',2,'Q',-1,0],
            ['Заметки','note',3,'T',NULL,0],
            ['ТС по деньгам','realmoney',4,'X', NULL,0],
        ]);



    }
    
    public function safeDown()
    {
        $this->dropTable('dbx_finance');
        $this->dropTable('balance_check');
        $this->dropTable('xlsx_column');
        $this->dropForeignKey('fk_record_xlsx_column','record');
        $this->dropForeignKey('fk_record_item','record');
        $this->dropTable('record');
        $this->dropTable('note');
        $this->dropTable('item');
    }

}
