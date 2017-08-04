<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "like".
 *
 * @property integer $id
 * @property integer $like
 * @property integer $user
 * @property integer $advert
 */
class Like extends \yii\db\ActiveRecord
{

    const Like = 1;
    const Dislike = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'like';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['like','advert','user'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'like' => 'Like',
        ];
    }

    public function LikeAdvert($type,$advert,$user){

        $user_like = Yii::$app->user->id;

        $like_advert = Like::findOne(['advert' => $advert]);

        if(Yii::$app->user->isGuest) {
            Yii::$app->session->addFlash('warning', 'Для добавления оценки нужна регистрация');
        } elseif ($user == $user_like){
            Yii::$app->session->addFlash('warning', 'Вы не можете оценить свое объявление');
        } elseif ($user_like == $like_advert['user']){
            Yii::$app->session->addFlash('warning', 'Вы уже оценили это объявление');
        } elseif($type == self::Like) {

            $like_advert = new Like();
            $like_advert['like'] = self::Like;
            $like_advert['advert'] = $advert;
            $like_advert['user'] = $user_like;
            $like_advert->save();

            $count_like = Like::findAll(['like'=>self::Like,'advert'=>$advert]);
            return count($count_like);

        } else {
            return self::Dislike;
        }
    }
}
