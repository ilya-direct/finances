<?php

namespace app\models\WalletDB;

use Yii;

/**
 * This is the model class for table "transaction_category".
 *
 * @property integer $id
 * @property string $name
 * @property string $sort
 * @property string $value
 * @property integer $deleted
 * @property string $sign
 */
class TransactionCategory extends \yii\db\ActiveRecord
{
    public $fields=[
        'Мама'=>            ['name'=>'p_mom_multiple','sort'=>1,'column'=>'F'],
        'PM' =>             ['name'=>'p_mompm','sort'=>2,'column'=>'H'],
        'Другие доходы'=>   ['name'=>'p_other_multiple','sort'=>3,'column'=>'I'],
        'МГТУ'=>            ['name'=>'m_university','sort'=>4,'column'=>'L'],
        'бенз'=>            ['name'=>'m_petrol','sort'=>5,'column'=>'M'],
        'Моб'=>             ['name'=>'m_mobile','sort'=>8,'column'=>'N'],
        'Развлечения'=>     ['name'=>'m_spend_multiple','sort'=>11,'column'=>'O'],
        'Другие расходы'=>  ['name'=>'m_other_multiple','sort'=>12,'column'=>'Q'],
        'Заметки'=>         ['name'=>'m_other_multiple','sort'=>12,'column'=>'T'],
        'Корректировка'=>   ['name'=>'correcting','sort'=>13,'column'=>'Y']
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['sort', 'deleted'], 'integer'],
            [['name', 'value'], 'string', 'max' => 30],
            [['sign'], 'string', 'max' => 1]
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
            'sort' => 'Sort',
            'value' => 'Value',
            'deleted' => 'Deleted',
            'sign' => 'Sign',
        ];
    }

	public function getRecords()
	{
		return $this->hasMany(Record::className(), ['tcategory' => 'id']);
	}
}
