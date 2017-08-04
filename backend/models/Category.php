<?php
/**
 * Created by PhpStorm.
 * User: gebruiker
 * Date: 20.01.17
 * Time: 13:59
 */

namespace backend\models;

use yii\db\ActiveRecord;


class Category extends  ActiveRecord{


    static $status=[
        0 => 'Не активна',
        1 => 'Активна'
    ];

    public function nameTable(){
        return 'Категории';
    }

    public function attributeLabels()
    {
        return [

            'id' => 'ID',
            'title' => 'Назва',
            'status' => 'Активна',
            'img' => 'Фото',
            'pref' => 'Url'
        ];
    }

    const SCENARIO_ADD = 'add';

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_ADD]=['title','pref','pref','img'];

        return $scenarios;
    }

    public function rules()
    {
        return [
            [['title','pref'],'required','on'=>self::SCENARIO_ADD],
            [['pref','img'],'safe','on'=>self::SCENARIO_ADD]

        ];
    }

    public function rows(){
        return [
            [
                'name'=>'id',
                'type'=>'input',
                'display'=>false,
                'attr'=>[
                    'disabled'=>'disabled'
                ]
            ],
            [
                'name'=>'title',
                'type'=>'input',
                'display'=>true,
                'attr'=>[
                    'data-pref'=>'out'
                ]
            ],
            [
                'name'=>'sub',
                'type'=>'tree',
                'display'=>false,
            ],
            [
                'name'=>'status',
                'type'=>'select',
                'display'=>true,
                'trueLabel'=>'Активна',
                'falseLabel'=>'Не активна',
                'data'=>self::$status
            ],
            [
                'name'=>'pref',
                'type'=>'input',
                'display'=>false,
                'attr'=>[
                    'data-pref'=>'in'
                ]
            ],
            [
                'name'=>'img',
                'type'=>'file',
                'display'=>false
            ]
        ];
    }

}