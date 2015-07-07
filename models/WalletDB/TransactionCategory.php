<?php

namespace app\models\WalletDB;

use Yii;

/**
 * This is the model class for table "transaction_category".
 *
 * @property integer $id
 * @property string $name
 * @property string $sort
 * @property string $value
 * @property integer $deleted
 * @property string $sign
 */
class TransactionCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['sort', 'deleted'], 'integer'],
            [['name', 'value'], 'string', 'max' => 30],
            [['sign'], 'string', 'max' => 1]
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
            'sort' => 'Sort',
            'value' => 'Value',
            'deleted' => 'Deleted',
            'sign' => 'Sign',
        ];
    }
}
