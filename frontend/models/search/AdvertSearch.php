<?php
/**
 * Created by PhpStorm.
 * User: boss
 * Date: 17.06.17
 * Time: 18:48
 */
namespace frontend\models\search;

use yii\data\ActiveDataProvider;
use frontend\models\Advert;

class AdvertSearch extends Advert {

    public static function tableName()
    {
        return '{{advert}}';
    }

    public function rules()
    {
        return [
            [['status', 'price','title'], 'safe'],
        ];
    }


    public function search($params)
    {
        $query = Advert::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'price' => $this->price,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}