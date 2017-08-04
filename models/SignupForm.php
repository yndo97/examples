<?php
namespace frontend\models;

use yii\base\Model;
use frontend\models\User;
use yii\base\Security;
use Yii;
use yii\helpers\HtmlPurifier;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $location;
    public $phone;
    public $password;
    public $agree;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value);
            }],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => 'frontend\models\User'],
            ['email', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value);
            }],
            ['location', 'required'],
            ['email', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value);
            }],
            ['location', 'string', 'max' => 70],
        ];
    }

    public function attributeLabels()
    {
        return
            [
                'location'=>'Местоположение',
                'username'=>'Контактное лицо',
                'email'=>'Email-адрес',
                'phone'=>'Номер телефона'
            ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup($phone)
    {
        if (!$this->validate()) {
            return null;
        }

        $password = SignupForm::GenPassword();
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->location = $this->location;
        $phone_arr = json_encode($phone);
        $user->phone = $phone_arr;
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        $user->created_at = date('Y-m-d H:i:s');
        $email = Settings::findOne(['name'=>'email']);

        if($user->save()){

            Yii::$app->mailer->compose('emailConfirm',['user'=>$user,'password'=>$password])
                ->setFrom($email['value'])
                ->setTo($this->email)
                ->setSubject('Подтверждение регистрации')
                ->send();

            return $user;
        }
    }


    public function GenPassword ($length=6) {
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $length = intval($length);
        $size=strlen($chars)-1;
        $password = "";
        while($length--) $password.=$chars[rand(0,$size)];
        return $password;
    }

}
