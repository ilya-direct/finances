<?php

namespace app\models\WalletDB;

use Dropbox\Exception_BadResponseCode;
use Yii;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property string $name
 * @property integer $correct_item_name_id
 */
class Item extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }


    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbFin2014');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 55],
            [['name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }


    public function getRecords()
    {
        return $this->hasMany(Record::className(), ['itemid' => 'id']);
    }



    static public function get_item_id($item_name){
        $item=trim($item_name);
        if(empty($item))
            throw new \Exception('Не допускается пустое имя у элемента!');
        $item_obj=self::findOne(['name'=>$item]);
        if(is_null($item_obj)){
            $item_obj = new self(['name' => $item]);
            $item_obj->save();
        }
        return $item_obj->id;
    }
}
