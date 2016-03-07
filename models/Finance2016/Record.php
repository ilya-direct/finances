<?php

namespace app\models\Finance2016;

use Dropbox\Exception;
use Yii;

/**
 * This is the model class for table "record".
 *
 * @property integer $id
 * @property string $date
 * @property integer $sum
 * @property integer $item_id
 *
 * @property Item $item
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
     */

    static public function insertSingle($date,$column,$sum){
        if($column->type!= XlsxColumn::TYPE_SINGLE){
            throw new \Exception("Column {$column->name} is not single");
        }
        $date=$date->format('Y-m-d');
        if(!is_null($column->sign))
            $sum=$column->sign / abs($column->sign) * abs($sum);
        else
            $sum= (int) $sum;

        $itemId=Item::getItemId($column->name);
        if((!$column->zero_sum && $sum==0) or !$itemId)
            throw new \Exception("\n\n Wrong record :{$date} Sum:{$sum} Item name: ".$date);
        $attributes=['date'=>$date,'item_id'=>$itemId,'xlsx_column_id'=>$column->id];
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
        $attributes=['date'=>$date,'item_id'=>$itemId,'xlsx_column_id'=>11];
        $rec=Record::findOne($attributes);
        $rec=is_object($rec) ? $rec : new Record($attributes);
        $rec->sum=(int) $sum;
        if(!$rec->save())
            throw new \Exception("Cannot save correction. Date: ".$date);
        return true;
    }

    static function deleteCorrection($date){
        $itemId=Item::getItemid('correction');
        self::deleteAll(['date'=>$date,'item_id'=>$itemId]);
    }

    static function clearDb($date,$column){
        switch($column->type){
            case XlsxColumn::TYPE_SINGLE:
            case XlsxColumn::TYPE_MULTIPLE:
                Record::deleteAll(['date'=>$date,'xlsx_column_id'=>$column->id]);
                break;
            case XlsxColumn::TYPE_NOTE:
                Note::deleteAll(['date'=>$date]);
                break;
            case XlsxColumn::TYPE_CHECKPOINT:
                BalanceCheck::deleteAll(['date'=>$date]);
                break;
            default:
                throw new \Exception("Undefined type for deletion");
                break;
        }
    }

    static function insertMultiple($date,$column,$cellValue,$descValue){
        if($column->type!= XlsxColumn::TYPE_MULTIPLE){
            throw new \Exception("Column {$column->name} is not multiple");
        }
        $coins=explode("\n",trim($cellValue));
        $coins_desc=explode("\n",trim($descValue));
        if (count($coins)!=count($coins_desc))
            throw new \Exception("Multiple record error {$date} : quantity sum not equal to desc {$column->name}");

        if (is_null($column->sign))
            throw new \Exception("Sign is not set for multiple column {$column->name}\n");

        $availableRecs=Record::find()
            ->where(['date'=>$date, 'xlsx_column_id'=>$column->id])
            ->all();

        for($j=0;$j<count($coins);$j++){
            $rec=array_shift($availableRecs);
            $rec=empty($rec) ? new self() : $rec;
            $rec->sum=$column->sign / abs($column->sign) * abs($coins[$j]);
            $rec->item_id=Item::getItemId($coins_desc[$j]);

            if (!$rec->sum)
                throw new \Exception("Empty sum for {$coins_desc[$j]}, date: $date, column: {$column->name}");
            $rec->xlsx_column_id=$column->id;
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
}
