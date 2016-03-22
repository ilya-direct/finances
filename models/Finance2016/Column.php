<?php

namespace app\models\Finance2016;

use Yii;

/**
 * This is the model class for table "column".
 * @property integer $id
 * @property integer $column_type_id
 * @property string $letter
 * @property integer $sign
 * @property integer $name
 * @property string $value
 * @property string $zero_sum
 * @property string $deleteds
 */
class Column extends \yii\db\ActiveRecord
{

    const TYPE_SINGLE = 1;
    const TYPE_MULTIPLE = 2;
    const TYPE_NOTE = 3;
    const TYPE_CHECKPOINT = 4;
    const TYPE_DATE = 5;
    const TYPE_CORRECTING = 6;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'column';
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
            [['type', 'letter', 'value'], 'required'],
            [['type', 'sign'], 'integer'],
            [['letter'], 'string', 'max' => 2],
            [['value'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'letter' => 'Letter',
            'sign' => 'Sign',
            'value' => 'Value',
        ];
    }


    static public function getDateLetter(){
        $letter=self::find()->select(['letter'])->where(['deleted'=>0,'name'=>'date'])->scalar();
        if($letter===false){
            throw new \Exception("Date header not found");
        }
        return $letter;
    }
}
