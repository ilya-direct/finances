<?php

namespace app\models\WalletDB;

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
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbFin2014');
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance_check';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['consider', 'diff'], 'required'],
            [['consider', 'realmoney', 'diff'], 'integer'],
            [['date'], 'unique']
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
            'realmoney' => 'Realmoney',
            'diff' => 'Diff',
        ];
    }
}
