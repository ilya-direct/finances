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
}
