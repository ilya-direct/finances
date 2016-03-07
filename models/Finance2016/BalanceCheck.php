<?php

namespace app\models\Finance2016;

use Yii;

/**
 * This is the model class for table "balance_check".
 *
 * @property integer $id
 * @property string $date
 * @property integer $consider
 * @property integer $realmoney
 * @property integer $diff
 */
class BalanceCheck extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance_check';
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
            [['date', 'consider', 'real', 'difference'], 'required'],
            [['date'], 'safe'],
            [['consider', 'real', 'difference'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'consider' => 'Consider',
            'real' => 'Real money',
            'difference' => 'Diff',
        ];
    }

    static public function insertCheckpoint($date,$consider,$realmoney,$correction){
        $balance_check=self::findOne(['date'=>$date]);
        $balance_check=is_object($balance_check) ? $balance_check : new self();
        $balance_check->attributes=[
            'date'=>$date,
            'consider'=>$consider,
            'real'=>$realmoney,
            'difference'=>$correction,
        ];
        if(!$balance_check->save())
            throw new \Exception("Cannot save checkpoint. Date: ".$date);
        return true;
    }
}
