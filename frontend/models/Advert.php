<?php

namespace frontend\models;

use frontend\models\Category;
use frontend\models\Valuta;
use Yii;

/**
 * This is the model class for table "advert".
 *
 * @property integer $id
 * @property string $title
 * @property integer $category
 * @property integer $vip
 * @property integer $top
 * @property string $article
 * @property string $main_currency
 * @property string $price
 * @property string $condition
 * @property string $valuta
 * @property string $created_at
 * @property string $intro
 * @property string $img
 * @property string $user
 * @property string $status
 * @property string $pref
 */
class Advert extends \yii\db\ActiveRecord
{

    const Active = 1;
    const Disabled = 0;

    public $url;
    public $count_browse;
    public $message;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advert';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['condition','price','intro','category','article','title'],'required'],
            [['valuta','pref','status','created_at'],'safe'],
            [['intro'], 'string','max'=>1400],
            [['title'], 'string', 'max' => 70],
            [['article'], 'string', 'max' => 14],
            [['price', 'condition'], 'string', 'max' => 20],
            [['img'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 8],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'category' => 'Тип детали',
            'article' => 'Артикул',
            'price' => 'Цена',
            'condition' => 'Состаяние',
            'intro' => 'Описание',
            'img' => 'Фотографии',
        ];
    }

    public function upload()
    {

        if ($this->validate()) {
            foreach ($this->img as $file) {
                $file->saveAs('../web/source/' . $file->baseName . '.' . $file->extension);
            }
            return true;
        } else {
            return false;
        }

    }

    public function getMessage(){

        return $this->hasMany(MessageUser::className(),['advert'=>'id'])->count();
    }

    public function getItemCategory()
    {
        return $this->hasOne(Category::className(),['id'=>'category']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Valuta::className(),['id'=>'valuta']);
    }

    public function getUserInfo()
    {
        return $this->hasOne(User::className(),['id'=>'user']);
    }

    public function getLike()
    {
        return $this->hasMany(Like::className(),['advert'=>'id']);
    }

    public function getCondition(){
        if($this->condition == 1){
            return 'Новый';
        } else {
            return 'Б/y';
        }
    }

    public function UrlAdvert($all_categories){

        $category = $this->itemCategory;

        $url_category = Category::CategoryParent($category['sub'],$all_categories,$category['pref']);

        $url_category = array_reverse($url_category);

        $url = ['advert/view-advert'];

        for ($i=0;$i<count($url_category);$i++){
            $level_up = $i+1;
            $url['level_'.$level_up] = $url_category[$i];
        }

        $url['pref'] = $this->pref;

       return $url;
    }

    public function SaveCurrency(){

        $currency = $this->currency;

        $this->main_currency = $currency['course'] * $this->price;

        return $this->main_currency;

    }

}
