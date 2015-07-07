<?php

namespace app\models\WalletDB;

use Yii;

/**
 * This is the model class for table "correct_item_name".
 *
 * @property integer $id
 * @property string $name
 */
class CorrectItemName extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'correct_item_name';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 30]
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
}
