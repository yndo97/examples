<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "characteristics_value".
 *
 * @property integer $id
 * @property string $value
 * @property integer $status
 */
class CharacteristicsValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'characteristics_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status','value'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Value',
            'status' => 'Status',
        ];
    }
}
