<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "message_user".
 *
 * @property integer $id
 * @property integer $sender
 * @property integer $recipient
 * @property integer $advert
 * @property string $text
 * @property string $file
 */
class MessageUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sender', 'recipient', 'advert'], 'integer'],
            [['text'], 'required'],
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
            'sender' => 'Sender',
            'recipient' => 'Recipient',
            'advert' => 'Advert',
            'text' => 'Текст',
            'file' => 'File',
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
