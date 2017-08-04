<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "services_advertising".
 *
 * @property integer $id
 * @property string $name
 * @property string $price
 * @property integer $group
 * @property integer $time
 * @property string $measuring_time
 * @property string $info
 * @property integer $count
 */
class ServicesAdvertising extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'services_advertising';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'number'],
            [['group', 'time', 'count'], 'integer'],
            [['name', 'info'], 'string', 'max' => 255],
            [['measuring_time'], 'string', 'max' => 20],
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
            'price' => 'Price',
            'group' => 'Group',
            'time' => 'Time',
            'measuring_time' => 'Measuring Time',
            'info' => 'Info',
            'count' => 'Count',
        ];
    }

    public function FormatTime(){

        if($this->measuring_time == 1){
            return 'Дней';
        } elseif ($this->measuring_time == 2){
            return 'Месяцев';
        }
    }

    public function countTime(){

        $time = [];

        for($i=0;$i<=$this->time;$i++){
            if($i > 0) {
                $time[] = ['day' => $i];
            }
        }

        $time = ArrayHelper::map($time,'day','day');

       return $time;
    }
}
