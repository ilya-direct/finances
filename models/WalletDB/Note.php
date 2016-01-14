<?php

namespace app\models\WalletDB;

use Yii;

/**
 * This is the model class for table "note".
 *
 * @property string $id
 * @property string $date
 * @property string $text
 */
class Note extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'text'], 'required'],
            [['date'], 'safe'],
            [['text'], 'string']
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
            'text' => 'Text',
        ];
    }
}
