<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $title
 * @property integer $status
 * @property string $pref
 * @property string $img
 * @property integer $sub
 */
class Category extends \yii\db\ActiveRecord
{

    public $level;
    public $next;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'sub'], 'integer'],
            [['title', 'pref', 'img'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'status' => 'Status',
            'pref' => 'Pref',
            'img' => 'Img',
            'sub' => 'Sub',
        ];
    }

    public function getParentCategory()
    {
        return $this->hasMany(Category::className(),['z'=>'sub']);
    }


    public function CategoryParent($sub,$categories,$pref = null){

        $url = [$pref];

        foreach ($categories as $category){

            if($category['id'] == $sub){

                if(isset($category['sub'])) {
                    $url = array_merge($url, Category::CategoryParent($category["sub"], $categories,$category['pref']));
                }

            }

        }

        return $url;
    }
}
