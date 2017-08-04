<?php

namespace frontend\models;

use Yii;
use yii\helpers\HtmlPurifier;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $location
 *
 * @property integer $phone
 */
class User extends ActiveRecord implements IdentityInterface
{

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public $password_repeat;
    public $password_new;
    public $password;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    const SCENARIO_CONTACT = 'contact';
    const SCENARIO_PASSWORD = 'password';

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CONTACT]=['phone','location','username','email'];
        $scenarios[self::SCENARIO_PASSWORD]=['password_new','password_repeat','password'];

        return $scenarios;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','password_hash', 'email', 'created_at'], 'safe'],
            [['status', 'created_at','phone'], 'safe'],
            ['email', 'unique', 'targetClass' => 'frontend\models\User','on'=>self::SCENARIO_CONTACT],
            ['email', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value);
            },'on'=>self::SCENARIO_CONTACT],
            [['username','location','phone'],'required','on'=>self::SCENARIO_CONTACT],
            ['password','validatePasswordOld','on'=>self::SCENARIO_PASSWORD],
            [['password_repeat','password','password_new'],'required','on' => self::SCENARIO_PASSWORD],
            ['password_repeat', 'compare', 'compareAttribute'=>'password_new','on' => self::SCENARIO_PASSWORD],
            [['username', 'password_hash', 'password_reset_token', 'email', 'location'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'location' => 'Location',
            'phone' => 'Phone',
        ];
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public function validatePasswordOld($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный пароль');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
//        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getUser(){
        $id = Yii::$app->user->id;
        $user = User::findOne($id);
        return $user;
    }

    public function editUser(){

        if(!$this->validate()){
            return false;
        }

        $this->phone = json_encode($this->phone);
        if($this->save()){
            Yii::$app->session->setFlash('warning','Контактные данные изменены');
            return true;
        }
    }

    public function newPassword(){

        if($this->validate()) {

            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password_new);

            if ($this->save()) {
                Yii::$app->session->setFlash('warning', 'Контактные данные изменены');
                return true;
            }

        } else {
            return false;
        }

    }

}
