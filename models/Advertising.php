<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "advertising".
 *
 * @property integer $id
 * @property integer $service
 * @property integer $advert
 * @property string $time
 */
class Advertising extends \yii\db\ActiveRecord
{
    const Top = 1;
    const Vip = 2;
    const Color = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advertising';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service', 'advert'], 'integer'],
            [['time'], 'safe'],
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
            'advert' => 'Advert',
            'time' => 'Time',
        ];
    }

    public function getAdvertItem()
    {
        return $this->hasOne(Advert::className(),['id'=>'advert']);
    }

}
