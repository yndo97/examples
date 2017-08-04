<?php
/**
 * Created by PhpStorm.
 * User: gebruiker
 * Date: 18.07.17
 * Time: 21:24
 */
namespace frontend\controllers;

use frontend\models\BrowsingAdvert;
use frontend\models\Category;
use frontend\models\CharacteristicsValue;
use frontend\models\Advertising;
use frontend\models\Characteristics;
use frontend\models\Complaint;
use frontend\models\ComplaintName;
use frontend\models\fun\HelpFunction;
use frontend\models\Advert;
use frontend\models\Like;
use frontend\models\MessageUser;
use frontend\models\User;
use frontend\models\Valuta;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;

class AdvertController extends Controller {

    public function beforeAction($action)
    {
        if($action->id == 'submit' or $action->id == 'vie-advert'){
            Url::remember(Yii::$app->request->url,'page'); //запам'ятовування URL
        }


        return parent::beforeAction($action);
    }


    public function actionSubmit(){

        $model = new Advert();
        $user = User::getUser();

        $condition = [
            1 =>  'Новый',
            2 => 'Б/y'
        ];

        $valuta = Valuta::find()->all();

        $characteristic_value = CharacteristicsValue::find()->all();

        $new_characteristic = new Characteristics();

        if($model->load(Yii::$app->request->post()) and $model->validate()){

            $currency = $_POST['valuta'];

            if(empty($_POST['valuta'])){
                $currency = 2;
            }

            $model->valuta = $currency;
            $model->status = 1;
            $model->saveCurrency();
            $model->user = $user['id'];
            $model->created_at = date('Y-m-d H:i:s');

            $model->img = UploadedFile::getInstances($model, 'img');

            if(!empty($model->img)) {
                $model->upload();
                $img_arr = $_FILES['Advert']['name']['img'];

                $img = [];
                foreach ($img_arr as $item) {
                    if (!empty($item)) {
                        $img[] = '/source/' . $item;
                    }
                }

                $model->img = json_encode($img);
            } else {
                $model->img = json_encode(['/source/default.jpg']);
            }

            if($model->save()){
                $id_model = Yii::$app->db->lastInsertID;
                $model->pref = $this->translit($model['title'].'-'.$id_model);
                $model->save();
                Characteristics::addCharacteristic($id_model);
                $session = Yii::$app->session;
                $session['new_advert'] = $id_model;
                return $this->redirect('publication');
            }

        }

        return $this->render('submit',compact('model','condition','valuta','characteristic_value','new_characteristic'));
    }

    public function actionPublication(){

        $session = Yii::$app->session;

        $advert = $session['new_advert'];

        $all_categories = Category::find()->all();

        $advert = Advert::findOne($advert);

        HelpFunction::FormatAdvert($advert,$all_categories);

        return $this->render('publication',compact('advert'));
    }


    public function actionViewAdvert(){

        $pref = $_GET['pref'];
        $session = Yii::$app->session;
        $all_categories = Category::find()->all();

        $advert = Advert::findOne(['pref'=>$pref]);

        BrowsingAdvert::PageBrowsing($advert['id']); //запис в бд перегляду

        $advert['category'] = $advert->itemCategory;
        $advert['valuta'] = $advert->currency;
        //відгуки користувачів
        $like = Like::Like; //тип оцінки
        $dislike = Like::Dislike;
        $like_count = Like::find()->where(['like'=>1,'advert'=>$advert['id']])->count();
        $dislike_count = Like::find()->where(['like'=>2,'advert'=>$advert['id']])->count();

        $advert['created_at'] = HelpFunction::rus_date('в H:i,d F Y',strtotime($advert['created_at']));
//        $advert['created_at'] = Yii::$app->formatter->asDatetime($advert['created_at'], "php:в H:i,d F Y");
        $advert->img = HelpFunction::editImg($advert['img']);
        //загальна кількість оголошень
        $advert_count = Advert::find()->where(['user'=>$advert['user'],'status'=>1])->count();
        //інші повідомлення автора
        $advert_other = Advert::find()->where(['and',['user'=>$advert['user']],['!=','id',$advert['id']],['status'=>1]])
            ->limit(5)->all();
        //кільскість наступних повідомлень
        $start = count($advert_other)+1;
        $number_start_advert = Advert::find()->where(['user'=>$advert['user'],'status'=>1])->offset($start)->limit(5)->all();
        $number_start_advert = count($number_start_advert);

        foreach ($advert_other as $item_other){
            HelpFunction::FormatAdvert($item_other,$all_categories);
        }

        $advert_other_html = $this->renderPartial('layout/_advert_other',['adverts'=>$advert_other]);
        //iнформація про користувача
        $user = $advert->userInfo;
        $user['phone'] = HelpFunction::formatPhone($user['phone']);

        $user['created_at'] =  HelpFunction::rus_date('d.m.Y',strtotime($user['created_at']));
        //назви скарг на автора
        $complaint = ComplaintName::find()->all();

        if($session->hasFlash('warning')){
            $session->getFlash('warning');
        }
        //модель для відправки форми
        $model = new Complaint();

        $vip = HelpFunction::AdvertisingService(Advertising::Vip,$all_categories);
        $html_vip = $this->renderPartial('layout/_vip',['items'=>$vip]);

        //повідомлення автору
        $model_message = new MessageUser();

        if($model_message->load(Yii::$app->request->post())){

            $model_message->file = UploadedFile::getInstance($model_message, 'file');

            if(!empty($model_message['file'])) {
                $model_message->upload();
                $file_user = ['/source/'.$model_message->file->baseName.'.'.$model_message->file->extension];
                $model_message->file = json_encode($file_user);
            }

            $model_message->sender = Yii::$app->user->id;
            $model_message->recipient = $user['id'];
            $model_message->advert = $advert['id'];

            if($model_message->save()){
                Yii::$app->session->setFlash('warning','Ваше письмо отправлено');
            }
        }

        return $this->render('catalog-item',compact('advert','user','like','dislike','complaint','model',
            'advert_other_html','advert_count','number_start_advert','html_vip','model_message','like_count','dislike_count'));
    }

    public function actionLike(){

        $type = $_POST['type'];
        $advert = $_POST['advert'];
        $user = $_POST['user'];

        $count = Like::LikeAdvert($type,$advert,$user);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return json_encode([
            'count_like' => $count,
        ]);

    }

    public function actionComplaint(){
        $model = new Complaint();
        $data = $_POST['Complaint'];
        $user_like = Yii::$app->user->id;

        if (Yii::$app->request->isPost) {

            $model->file = UploadedFile::getInstance($model, 'file');

            if(!empty($model->file) and  $model->upload()) {
                $file = ['/source/' . $model->file->baseName . '.' . $model->file->extension];
                $model->file = json_encode($file);
            }

            $model->text = $data['text'];
            $model->advert = $data['advert'];
            $model->user = $user_like;
            $model->complaint = $data['complaint'];

            if($model->save()){
                $advert = $data['advert'];
                $like_advert = Like::findOne(['advert' => $advert]);

                if (empty($like_advert)) {
                    $like_advert = new Like();
                }

                $like_advert->like = Like::Dislike;
                $like_advert->advert = $advert;
                $like_advert->user = $user_like;

                if($like_advert->save()){
                   return $this->redirect(Yii::$app->request->referrer);
                }

            }
        }

    }

    public function actionAdvertOther(){

        $start = $_POST['start'];
        $user = Yii::$app->user->id;

        $all_categories = Category::find()->all();
        $start_next = $start + 1;
        $start = ($start - 1) * 5;

        $advert_other = Advert::find()->where(['and',['user'=>$user,'status'=>1],['!=','id',$advert]])->offset($start)->limit(5)->all();

        $start = ($start_next - 1)*5;
        $next = Advert::find()->where(['and',['user'=>$user,'status'=>1],['!=','id',$advert]])->offset($start)->limit(5)->all();
        $count_other = count($next);

        $number_start_advert = $start_next;

        foreach ($advert_other as $item_other){
            HelpFunction::FormatAdvert($item_other,$all_categories);
        }

        $advert_other_html = $this->renderPartial('layout/_advert_other',['adverts'=>$advert_other]);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return json_encode([
            'advert_other' => $advert_other_html,
            'count' => $count_other,
            'start' => $number_start_advert
        ]);
    }
}