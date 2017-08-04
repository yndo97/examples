<?php
namespace frontend\controllers;

use frontend\models\Advertising;
use frontend\models\Category;
use frontend\models\fun\HelpFunction;
use frontend\models\GroupAdvertising;
use frontend\models\ServicesAdvertising;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\AccessControl;
use frontend\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\data\Pagination;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $all_categories = Category::find()->all();

        $vip = HelpFunction::AdvertisingService(Advertising::Vip,$all_categories);
        $top = HelpFunction::AdvertisingService(Advertising::Top,$all_categories);

        $query = HelpFunction::QueryFilter();

        $page = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 5,
            'pageSizeParam' => false,
        ]);

        $page_number = isset($_REQUEST['page']) ? ($_REQUEST['page']) : 1;

        $row_start = ($page_number - 1) * 5;  //число зміщення вибірки

        $other_adverts = $query->offset($row_start)->limit(5)->all();

       foreach ($other_adverts as $other_advert){
           HelpFunction::FormatAdvert($other_advert,$all_categories);
       }

        $html_top = $this->renderPartial('../advert/layout/_classic_view', ['items' => $top,'class'=>'common_note',
            'type_name'=>'Топ - объявления']);
        $html_other = $this->renderPartial('../advert/layout/_index_other', ['items' => $other_adverts,'class'=>'user_note',
            'type_name'=>'Обычные']);


       if(isset($session['view']) and  $session['view'] == 'block'){

           $html_top = $this->renderPartial('../advert/layout/_view_classic_block',['items' => $top,'class'=>'common_note cube_position',
               'type_name'=>'Топ - объявления','type'=>'top']);
           $html_other = $this->renderPartial('../advert/layout/_view_classic_block', ['items' => $other_adverts,'class'=>'user_note cube_position',
               'type_name'=>'Обычные','type'=>'classic']);
       }

        $html_vip = $this->renderPartial('../advert/layout/_vip', ['items' => $vip]);

        return $this->render('index',compact('html_top','html_vip','html_other','page'));
    }

    public function actionConfirm()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('/');
        } else {
            return $this->render('confirm', [
                'model' => $model,
            ]);
        }
    }

    public function actionServiceAdvertising(){

        $service_group_1 = GroupAdvertising::find()->where(['type_group'=>1])->orderBy('status desc')->all();

        $price_1 = 0;
        foreach ($service_group_1 as $service_1){
            if(empty($service_1['time'])){
                $service_1['time'] = 'N';
            }
            $service_1['service'] = $service_1->itemService;
            $price_1 += $service_1['service']['price'];
        }

        $group_1 = $this->renderPartial('layout/_group_advertising',['icon'=>'fa fa-paper-plane',
            'class'=>'package-item','name'=>'Пакет 1','services'=>$service_group_1,'price'=>$price_1]);

        $service_group_2 = GroupAdvertising::find()->where(['type_group'=>2])->orderBy('status desc')->all();

        $price_2 = 0;
        foreach ($service_group_2 as $service_2){
            if(empty($service_2['time'])){
                $service_2['time'] = 'N';
            }
            $service_2['service'] = $service_2->itemService;
            $price_2 += $service_2['service']['price'];

        }

        $group_2 = $this->renderPartial('layout/_group_advertising',['icon'=>'fa fa-lightbulb-o',
            'class'=>'package-item active','name'=>'Пакет 2','services'=>$service_group_2,'price'=>$price_2]);

        $service_group_3 = GroupAdvertising::find()->where(['type_group'=>3])->orderBy('status desc')->all();

        $price_3 = 0;
        foreach ($service_group_3 as $service_3){
            if(empty($service_3['time'])){
                $service_3['time'] = 'N';
            }
            $service_3['service'] = $service_3->itemService;
            $price_3 += $service_3['service']['price'];
        }

        $group_3 = $this->renderPartial('layout/_group_advertising',['icon'=>'fa fa-diamond',
            'class'=>'package-item','name'=>'Пакет 3','services'=>$service_group_3,'price'=>$price_3]);

        $services = ServicesAdvertising::find()->all();

        foreach ($services as $service){
            $service['measuring_time'] = $service->FormatTime();
            $service['time'] = $service->countTime();
        }

        return $this->render('advertising_services',compact('group_1','group_2','group_3','services'));
    }

    public function actionView(){
        $type = $_POST['type'];
        $session = Yii::$app->session;
        $session['view'] = $type;
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignUp()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            $phone = $_POST['phone'];
            if ($user = $model->signup($phone)) {
               return $this->redirect('confirm');
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionLogin(){

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('/');
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
}
