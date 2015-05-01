<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "company_assign".
 *
 * @property integer $id
 * @property integer $price
 * @property integer $company_id
 * @property integer $device_id
 * @property integer $service_id
 */
class CompanyAssign extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company_assign';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price','company_id', 'device_id', 'service_id'], 'required'],
            [['price','company_id', 'device_id', 'service_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'price' => 'Цена',
            'company_id' => 'Компания',
            'device_id' => 'Устройство',
            'service_id' => 'Услуга',
        ];
    }

	public function getCompany()
	{
		return $this->hasOne(Company::className(), ['company_id' => 'id']);
	}

	public function getDevice()
	{
		return $this->hasOne(Device::className(), ['device_id' => 'id']);
	}

	public function getService()
	{
		return $this->hasOne(Service::className(), ['service_id' => 'id']);
	}
}
