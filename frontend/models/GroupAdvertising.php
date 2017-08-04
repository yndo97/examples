<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "group_advertising".
 *
 * @property integer $id
 * @property integer $service
 * @property integer $status
 * @property integer $type_group
 * @property integer $time
 */
class GroupAdvertising extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_advertising';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service', 'status', 'type_group', 'time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service' => 'Service',
            'status' => 'Status',
            'type_group' => 'Type Group',
            'time' => 'Time',
        ];
    }

    public function getItemService(){

        return $this->hasOne(ServicesAdvertising::className(),['id'=>'service']);
    }

}
