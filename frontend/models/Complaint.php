<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "complaint".
 *
 * @property integer $id
 * @property integer $user
 * @property string $text
 * @property string $file
 * @property string $advert
 * @property integer $complaint
 */
class Complaint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'complaint';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user', 'complaint'], 'integer'],
            ['complaint', 'safe'],
            ['file', 'safe'],
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg,xls,doc'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user' => 'User',
            'text' => 'Text',
            'file' => 'File',
            'complaint' => 'Complaint',
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $this->file->saveAs('../web/source/' . $this->file->baseName . '.' . $this->file->extension);
            return true;
        } else {
            return false;
        }
    }
}
