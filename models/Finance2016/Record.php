<?php

namespace app\models\Finance2016;

use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "record".
 *
 * @property integer $id
 * @property string $date
 * @property integer $sum
 * @property integer $item_id
 * @property integer $column_id
 *
 * @property Item $item
 */
class Record extends Yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'record';
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
            [['date', 'sum', 'item_id'], 'required'],
            [['date'], 'safe'],
            [['sum', 'item_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'sum' => 'Sum',
            'item_id' => 'Item ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    /**
     * @param $date \DateTime
     * @param $column
     * @param $sum
     * @throws \Exception
     * @return bool
     */

    static public function insertSingle($date,$column,$sum){
        if($column->column_type_id!= Column::TYPE_SINGLE){
            throw new \Exception("Column {$column->name} is not single");
        }
        $date=$date->format('Y-m-d');

        $sum=self::getSign($column->sign) * abs($sum);

        $itemId=Item::getItemId($column->name);
        if((!$column->zero_sum && $sum==0) or !$itemId)
            throw new \Exception("\n\n Wrong record :{$date} Sum:{$sum} Item name: ".$date);
        $attributes=['date'=>$date,'item_id'=>$itemId,'column_id'=>$column->id];
        $recs=Record::findAll($attributes);
        if(count($recs)>1){
            throw new \Exception("More than one record in single column ".$column->name." date: ".$date);
        }
        $rec=array_shift($recs);
        $rec=is_object($rec) ? $rec : new Record($attributes);
        $rec->sum=$sum;
        if(!$rec->save())
            throw new \Exception("Cannot save record. Date: ".$date.", Item name: ". $column->name);
        return true;
    }


    static function setCorrection($date,$sum){

        $itemId=Item::getItemId('correction');
        $column = Column::findOne(['name' => 'correction']);
        $attributes=['date'=>$date,'item_id'=>$itemId,'column_id'=>$column->id];
        $rec=Record::findOne($attributes);
        $rec=is_object($rec) ? $rec : new Record($attributes);
        $rec->sum=(int) $sum;
        if(!$rec->save())
            throw new \Exception("Cannot save correction. Date: ".$date);
        return true;
    }

    static function deleteCorrection($date){
        $itemId=Item::getItemId('correction');
        self::deleteAll(['date'=>$date,'item_id'=>$itemId]);
    }

    /**
     * @param $date
     * @param $column \app\models\Finance2016\Column
     * @throws \Exception
     */
    static function clearDb($date, $column)
    {
        switch ($column->column_type_id) {
            case Column::TYPE_SINGLE:
            case Column::TYPE_MULTIPLE:
                Record::deleteAll(['date' => $date, 'column_id' => $column->id]);
                break;
            case Column::TYPE_NOTE:
                Note::deleteAll(['date' => $date]);
                break;
            case Column::TYPE_CHECKPOINT:
                BalanceCheck::deleteAll(['date' => $date]);
                break;
            case Column::TYPE_CORRECTING:
                break;
            default:
                throw new Exception("Undefined type for deletion");
                break;
        }
    }

    static function getSign($sign) {
        if ($sign === '+'){
            return 1;
        } elseif ($sign === '-') {
            return -1;
        }else{
            throw new \Exception("Underfined sign!! \n");
        }
    }

    static function insertMultiple($date,$column,$cellValue,$descValue){
        if($column->column_type_id!= Column::TYPE_MULTIPLE){
            throw new \Exception("Column {$column->name} is not multiple");
        }
        $coins=explode("\n",trim($cellValue));
        $coins_desc=explode("\n",trim($descValue));
        if (count($coins)!=count($coins_desc))
            throw new \Exception("Multiple record error {$date} : quantity sum not equal to desc {$column->name}");

        $availableRecs=Record::find()
            ->where(['date'=>$date, 'column_id'=>$column->id])
            ->all();

        for($j=0;$j<count($coins);$j++){
            $rec=array_shift($availableRecs);
            $rec = empty($rec) ? new self() : $rec;
            $rec->sum = self::getSign($column->sign) * abs($coins[$j]);
            $rec->item_id = Item::getItemId($coins_desc[$j]);

            if (!$rec->sum)
                throw new \Exception("Empty sum for {$coins_desc[$j]}, date: $date, column: {$column->name}");
            $rec->column_id=$column->id;
            $rec->date=$date;
            if(!$rec->save()){
                throw new \Exception("Cannot save {$coins_desc[$j]}: {$coins[$j]}, date: $date, column: {$column->name}");
            }
        }

        if (!empty($availableRecs)){
            $ids_for_delete=array_column($availableRecs,'id');
            Record::deleteAll(['in','id',$ids_for_delete]);
        }

    }
    
    static public function insert_transaction_multiple($date,$tcategory,$entries){
        $tcategory_parts=explode('_',$tcategory);
        if(count($tcategory_parts)!==3 or $tcategory_parts[2]!='multiple')
            throw new \Exception("Категория tcategory не является multiple $tcategory\n $date");
        list($y,$m,$d)=explode('.',$date);
        $tc=Column::find()->select(['id','value','sign'])->where(['name'=>$tcategory,'deleted'=>0])->one();
        if(!$tc->id or !checkdate($m,$d,$y))
            throw new \Exception("Несуществующая категория или неверная дата $tc->id $date\n");
        
        if ($tc->sign=='+') $sign=1;
        elseif($tc->sign=='-') $sign=-1;
        else
            throw new \Exception("Не указан знак категории multiple $tcategory\n");
        
        $available_recs=Record::find()->where(['date'=>$date, 'column_id'=>$tc->id])->all();
        
        foreach($entries as $entry){
            $rec=array_shift($available_recs);
            
            $rec=empty($rec) ? new Record() : $rec;
            $rec->sum=$sign * abs((int)$entry->sum);
            $rec->item_id=Item::getItemId($entry->item);
            if (!$rec->sum or !$rec->item_id)
                throw new \Exception("Нет суммы или пусное описание $date : $entry->sum $entry->item");
            $rec->column_id=$tc->id;
            $rec->date=$date;
            $rec->save();
        }
        
        // TODO: улучшить код ниже
        $available_ids=array_map(function($el){
            return $el['id'];
        },$available_recs);
        if (!empty($available_ids)){
            Record::deleteAll(['in','id',$available_ids]);
        }
    }
    
    static public function insert_transaction_single($date,$tcategory,$sum,$with_zero_sum=false){
        $tcategory_parts=explode('_',$tcategory);
        if(!in_array(count($tcategory_parts),[1,2]))
            throw new \Exception("Категория $tcategory не является single  $date");
        // TODO: проверка на то, если отсутствует категория
        $tc=Column::find()->select(['id','value','sign'])->where(['name'=>$tcategory,'deleted'=>0])->one();
        if(is_null($tc))
            throw new \Exception("\nНеверное название категории. Категория:{$tcategory}");
        $sum=(int) $sum;
        if ($tc->sign=='+') $sum=abs($sum);
        elseif ($tc->sign=='-') $sum=-abs($sum);
        $item_id=Item::getItemId($tc->value);
        list($y,$m,$d)=explode('.',$date);
        if((!$with_zero_sum && $sum==0) or !$item_id or !checkdate($m,$d,$y))
            throw new \Exception("\n\nНеверная транзакция Дата:{$date} Сумма:{$sum} Имя:{$tc->value} Категория:{$tcategory}");
        $params=['date'=>$date,'column_id'=>$tc->id,'item_id'=>$item_id];
        $recs=Record::find()->where($params)->all();
        $rec=array_shift($recs);
        if(!empty($recs)){
            throw new \Exception("Найдено более одной записи в single категории ".$tcategory." дата: ".$date);
        }
        
        $rec=is_object($rec) ? $rec : new Record($params);
        $rec->sum=$sum;
        $rec->save();
    }
}
