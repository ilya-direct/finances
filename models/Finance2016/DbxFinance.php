<?php

namespace app\models\Finance2016;

use Yii;

/**
 * This is the model class for table "dbx_finance".
 *
 * @property integer $id
 * @property integer $month
 * @property string $monthStr
 * @property integer $year
 * @property string $modified_time
 * @property string $downloaded_time
 * @property boolean $downloaded
 * @property boolean $in_db
 * @property string $file_name
 *
 *
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
            [['month', 'year'], 'required'],
            [['month', 'year'], 'integer'],
            [['modified_time', 'downloaded_time'], 'safe'],
            [['downloaded', 'in_db'], 'boolean'],
            [['file_name'], 'string', 'max' => 255]
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
            'modified_time' => 'Modified Time',
            'downloaded_time' => 'Downloaded Time',
            'downloaded' => 'Downloaded',
            'in_db' => 'In Db',
            'file_name' => 'File Name',
        ];
    }


    public function getMonthStr(){
        return str_replace(' ','0',\sprintf('%2.d', $this->month));
    }
}
