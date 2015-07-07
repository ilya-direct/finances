<?php

namespace app\models\CompanyDB;

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
            [['name', 'link'], 'string','min'=>4, 'max' => 70],
            [['advantage', 'disadvantage', 'comment', 'backtrace'], 'string', 'max' => 255],
            [['name'], 'unique'],
	        ['advantage','default', 'value' => null],
	        ['comment','validateComment','skipOnEmpty'=>true],
	        ['comment','default', 'value' => NULL],
        ];
    }

	public function validateComment($attribute)
    {
	    $value = $this->$attribute;

	    if (!preg_match('/mac/',$value)) {
		   // $this->addError($attribute, "((((");
	    }
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => 'Имя компании',
            'link' => 'Ссылка',
            'advantage' => 'Преимущества',
            'disadvantage' => 'Недостатки',
            'timecreated' => 'Время создания',
            'comment' => 'Комментарий',
            'backtrace' => 'Как нашёл',
        ];
    }

	public function getCompanyAssign()
	{
		return $this->hasMany(CompanyAssign::className(), ['company_id' => 'id']);
	}

	public static function getColumns($tablevar='c'){
		$params=[];
		$tablename=self::tableName();
		$colums=['id','name','link','advantage','disadvantage','timecreated','comment','backtrace'];
		foreach($colums as $col){
			$params[$tablename.'_'.$col]=$tablevar.'.'.$col;
		}
		return $params;
	}
}
