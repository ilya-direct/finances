<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property integer $id
 * @property string $name
 * @property string $link
 * @property string $advantage
 * @property string $disadvantage
 * @property string $timecreated
 * @property string $comment
 * @property string $backtrace
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['timecreated'], 'safe'],
            [['name', 'link'], 'string', 'max' => 45],
            [['advantage', 'disadvantage', 'comment', 'backtrace'], 'string', 'max' => 100],
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
            'link' => 'Link',
            'advantage' => 'Advantage',
            'disadvantage' => 'Disadvantage',
            'timecreated' => 'Timecreated',
            'comment' => 'Comment',
            'backtrace' => 'Backtrace',
        ];
    }

	public function getCompanyAssign()
	{
		return $this->hasMany(CompanyAssign::className(), ['company_id' => 'id']);
	}
}
