<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "browsing_page".
 *
 * @property integer $id
 * @property string $ip
 * @property integer $advert
 */
class BrowsingAdvert extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'browsing_advert';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'advert'], 'required'],
            [['advert'], 'integer'],
            [['ip'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'page' => 'Page',
        ];
    }

    public function PageBrowsing($page_id){
        $userIp = Yii::$app->request->userIp;
        $data_browsing = BrowsingAdvert::findOne(['ip'=>$userIp,'advert'=>$page_id]);
        if(empty($data_browsing)){
            $model = new BrowsingAdvert();
            $model->ip = $userIp;
            $model->advert = $page_id;
            $model->save();
        }

    }
}
