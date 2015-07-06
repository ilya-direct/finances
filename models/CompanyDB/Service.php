<?php

namespace app\models\CompanyDB;

use Yii;

/**
 * This is the model class for table "service".
 *
 * @property integer $id
 * @property string $name
 */
class Service extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
        ];
    }

	public function getCompanyAssign(){
		return $this->hasMany(CompanyAssign::className(), ['id' => 'service_id']);
	}

	public static function getColumns($tablevar='s'){
		$params=[];
		$tablename=self::tableName();
		$colums=['id','name'];
		foreach($colums as $col){
			$params[$tablename.'_'.$col]=$tablevar.'.'.$col;
		}
		return $params;
	}
}
