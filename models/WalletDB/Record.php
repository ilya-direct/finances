<?php

namespace app\models\WalletDB;

use Yii;

/**
 * This is the model class for table "record".
 *
 * @property integer $id
 * @property integer $sum
 * @property string $date
 * @property integer $itemid
 * @property integer $tcategory
 */
class Record extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sum', 'itemid', 'tcategory'], 'integer'],
            [['date'], 'safe'],
            [['itemid', 'tcategory'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sum' => 'Sum',
            'date' => 'Date',
            'itemid' => 'Itemid',
            'tcategory' => 'Tcategory',
        ];
    }

	public function getItem()
	{
		return $this->hasOne(Item::className(), ['id' => 'itemid']);
	}

	public function getTcategory()
	{
		return $this->hasOne(TransactionCategory::className(), ['id' => 'tcategory']);
	}

	static public function insert_transaction_multiple($date,$tcategory,$entries){
		$tcategory_parts=explode('_',$tcategory);
		if(count($tcategory_parts)!==3 or $tcategory_parts[2]!='multiple')
			throw new \Exception("Категория tcategory не является multiple $tcategory\n $date");
		list($y,$m,$d)=explode('.',$date);
		$tc=TransactionCategory::find()->select('id','value','sign')->where(['name'=>$tcategory,'deleted'=>0]);
		if(!$tc->id or !checkdate($m,$d,$y))
			throw new \Exception("Несуществующая категория или неверная дата $tc->id $date\n");

		if ($tc->sign=='+') $sign=1;
		elseif($tc->sign=='-') $sign=-1;
		else
			throw new \Exception("Не указан знак категории multiple $tcategory\n");

		$available_ids=Record::find()->select('id')->where(['date'=>$date, 'tcategory'=>$tc->id]);
		foreach($entries as $entry){
			$rec=new Record();
			$rec->sum=$sign * abs((int)$entry->sum);
			$rec->itemid=Item::get_item_id($entry->item);
			if (!$rec->sum or !$rec->itemid)
				throw new \Exception("Нет суммы или пусное описание $date : $entry->sum $entry->item");
			$rec->tcategory=$tc->id;
			$rec->date=$date;
			$rec->id=array_shift($available_ids);
			if(is_null($entry->id)){
				$rec->insert();
			}else{
				$rec->update();
			}
		}
		if (!empty($available_ids)){
			Record::deleteAll(['in','id',$available_ids]);
		}
	}

	static public function insert_transaction_single($date,$tcategory,$sum,$with_zero_sum=false){
		$tcategory_parts=explode('_',$tcategory);
		if(count($tcategory_parts)!==2)
			throw new \Exception("Категория tcategory не является single $tcategory\n $date");
		// TODO: проверка на то, если отсутствует категория
		$tc=TransactionCategory::find()->select('id','value','sign')->where(['name'=>$tcategory,'deleted'=>0]);
		$sum=(int) $sum;
		if ($tc->sign=='+') $sum=abs($sum);
		elseif ($tc->sign=='-') $sum=-abs($sum);
		$item_id=Item::get_item_id($tc->value);
		list($y,$m,$d)=explode('.',$date);
		if((!$with_zero_sum && $sum==0) or !$item_id or !checkdate($m,$d,$y))
			throw new \Exception("\n\nНеверная транзакция Дата:{$date} Сумма:{$sum} Имя:{$tc->value} Категория:{$tcategory}");
		$params=['date'=>$date,'tcategory'=>$tc->id,'itemid'=>$item_id];
		$recs=Record::find()->where($params);
		$rec=array_shift($recs);
		if(!empty($recs)){
			throw new \Exception("Найдено более одной записи в single категории ".$tcategory." дата: ".$date);
		}

		$rec=is_object($rec) ? $rec : new Record($params);
		$rec->sum=$sum;
		$rec->save();
	}
}
