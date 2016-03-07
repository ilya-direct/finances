<?php

namespace app\models\Finance2016;

use Dropbox\Exception;
use Yii;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Record[] $records
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
        return Yii::$app->get('dbFin2016');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100]
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecords()
    {
        return $this->hasMany(Record::className(), ['item_id' => 'id']);
    }

    static public function getItemid($name){
        $name=trim($name);
        if(empty($name)){
            throw new \Exception('Cannot create item with empty name');
        }
        $item=self::findOne(['name'=>$name]);
        if(is_null($item)){
            $item=new self(['name'=>$name]);
            if(!$item->save()){
                throw new \Exception('Cannot save item - '.$name);
            }
        }
        return (int)$item->id;
    }
}
