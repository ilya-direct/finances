<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "device".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 */
class Device extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'device';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 65],
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
            'type' => 'Type',
        ];
    }

	public function getCompanyAssign(){
		return $this->hasMany(CompanyAssign::className(), ['id' => 'device_id']);
	}
}
