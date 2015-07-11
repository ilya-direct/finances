<?php

namespace app\models\WalletDB;

use Yii;

/**
 * This is the model class for table "dbx_finance".
 *
 * @property integer $id
 * @property integer $month
 * @property string $year
 * @property string $file_name
 * @property string $modified_time
 * @property string $download_time
 * @property integer $exists
 * @property integer $csv_converted
 * @property integer $in_db
 */
class DbxFinance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dbx_finance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['month', 'year'], 'required'],
            [['month', 'exists', 'csv_converted', 'in_db'], 'integer'],
            [['year', 'modified_time', 'download_time'], 'safe'],
            [['file_name'], 'string', 'max' => 65],
            [['month', 'year'], 'unique', 'targetAttribute' => ['month', 'year'], 'message' => 'The combination of Month and Year has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'month' => 'Month',
            'year' => 'Year',
            'file_name' => 'File Name',
            'modified_time' => 'Modified Time',
            'download_time' => 'Download Time',
            'exists' => 'Exists',
            'csv_converted' => 'Csv Converted',
            'in_db' => 'In Db',
        ];
    }

	public function getMonthStr(){
		return str_replace(' ','0',\sprintf('%2.d', $this->month));
	}

	public function setMonthStr($value){
		$this->month=(int) $value;
	}
}
