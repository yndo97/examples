<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "characteristics".
 *
 * @property integer $id
 * @property integer $advert
 * @property integer $value
 * @property integer $characteristics
 */
class Characteristics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'characteristics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advert', 'value', 'characteristics'], 'required'],
            [['advert', 'value', 'characteristics'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'advert' => 'Advert',
            'value' => 'Value',
            'characteristics' => 'Characteristics',
        ];
    }

    public function addCharacteristic($advert){
        if (isset($_POST['Characteristic'])){

            $model = new Characteristics();

            foreach ($_POST['Characteristic'] as $key=>$value){

                $model->value = $value;
                $model->characteristics = $key;
                $model->advert = $advert;

                if($model->save()){
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}
