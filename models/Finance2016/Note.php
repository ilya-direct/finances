<?php

namespace app\models\Finance2016;

use Yii;

/**
 * This is the model class for table "note".
 *
 * @property integer $id
 * @property string $date
 * @property string $text
 */
class Note extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note';
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
            [['date', 'text'], 'required'],
            [['date'], 'safe'],
            [['text'], 'string']
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
            'text' => 'Text',
        ];
    }

    static public function insertNote($date,$value){
        $note=self::findOne(['date'=>$date]);
        $note=is_object($note) ? $note : new self();
        $note->date=$date;
        $note->text=$value;
        if(!$note->save())
            throw new \Exception("Cannot save note. Date: ".$date);
        return true;
    }
}
