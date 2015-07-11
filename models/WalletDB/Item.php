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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['correct_item_name_id'], 'integer'],
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
            'correct_item_name_id' => 'Correct Item Name ID',
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
		$item_obj=self::find()->where(['name'=>$item]);
		if(is_null($item_obj){
			$item_obj = new self(['name' => $item]);
			$item_obj->save();
		}
		return $item_obj->id;
	}
}
