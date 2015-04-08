<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "photo".
 *
 * @property integer $id
 * @property string $timecreated
 * @property string $link
 * @property integer $company_id
 */
class photo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'photo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['timecreated'], 'safe'],
            [['link', 'company_id'], 'required'],
            [['company_id'], 'integer'],
            [['link'], 'string', 'max' => 65]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'timecreated' => 'Timecreated',
            'link' => 'Link',
            'company_id' => 'Company ID',
        ];
    }
}
