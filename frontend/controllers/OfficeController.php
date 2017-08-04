<?php
/**
 * Created by PhpStorm.
 * User: gebruiker
 * Date: 01.08.17
 * Time: 12:38
 */
namespace frontend\controllers;

use frontend\models\Advert;
use frontend\models\BrowsingAdvert;
use frontend\models\Category;
use frontend\models\fun\HelpFunction;
use frontend\models\User;
use yii\web\Controller;
use Yii;

class OfficeController extends Controller {

    public function actionSetting(){

        $user = User::getUser();
        $class_contact = '';
        $phone = json_decode($user['phone']);
        $user->scenario = User::SCENARIO_CONTACT;

        if($user->load(\Yii::$app->request->post()) && isset($_POST['contact_user'])){

            if($user->editUser()){
                return $this->refresh();
            } else {
                $class_contact = 'active';
            }
        }

        $password = User::getUser();
        $password->scenario  = User::SCENARIO_PASSWORD;
        $class_password = '';

        if($password->load(Yii::$app->request->post()) && isset($_POST['password_user'])){

            if($password->newPassword()){
               return $this->refresh();
            } else {
                $class_password = 'active';
            }
        }

        return $this->render('setting',compact('user','password','class','class_contact','class_password','phone'));
    }

    public function actionMyAdvert(){

        $user = User::getUser();

        $adverts_disabled = HelpFunction::SortFilter(Advert::Disabled,0,'disabled');
        $adverts = HelpFunction::SortFilter(Advert::Active,0,'active');
        $adverts_all = HelpFunction::SortFilter('all',0,'all');

        $all_categories = Category::find()->all();

        $name_active = HelpFunction::TypeSort('name','active');
        $price_active = HelpFunction::TypeSort('price','active');
        $date_active = HelpFunction::TypeSort('date','active');
        $message_active = HelpFunction::TypeSort('message','active');

        $name_disabled = HelpFunction::TypeSort('name','disabled');
        $price_disabled = HelpFunction::TypeSort('price','disabled');
        $date_disabled = HelpFunction::TypeSort('date','disabled');
        $message_disabled = HelpFunction::TypeSort('message','disabled');

        $name_all = HelpFunction::TypeSort('name','all');
        $price_all = HelpFunction::TypeSort('price','all');
        $date_all = HelpFunction::TypeSort('date','all');
        $message_all = HelpFunction::TypeSort('message','all');

        $sort_active = $this->renderPartial('layout/_sort',['name'=>$name_active,'price'=>$price_active,
            'date'=>$date_active,'message'=>$message_active,'type'=>'active','tabs'=>'tabs-1']);

        $sort_disabled = $this->renderPartial('layout/_sort',['name'=>$name_disabled,'price'=>$price_disabled,
            'date'=>$date_disabled,'message'=>$message_disabled,'type'=>'disabled','tabs'=>'tabs-2']);

        $sort_all = $this->renderPartial('layout/_sort',['name'=>$name_all,'price'=>$price_all,
            'date'=>$date_all,'message'=>$message_all,'type'=>'all','tabs'=>'tabs-3']);

        foreach ($adverts  as $advert){
            HelpFunction::FormatAdvert($advert,$all_categories);
            $browse = BrowsingAdvert::find()->where(['advert'=>$advert['id']])->all();
            $advert['count_browse'] = count($browse);
        }

        foreach ($adverts_disabled as $advert_disabled){
            HelpFunction::FormatAdvert($advert_disabled,$all_categories);
            $browse_disabled = BrowsingAdvert::find()->where(['advert'=>$advert_disabled['id']])->all();
            $advert_disabled['count_browse'] = count($browse_disabled);
        }

        foreach ($adverts_all as $item){
            HelpFunction::FormatAdvert($item,$all_categories);
            $browse_item = BrowsingAdvert::find()->where(['advert'=>$item['id']])->all();
            $item['count_browse'] = count($browse_item);
        }
//      активні оголошення
        $html_advert = $this->renderPartial('layout/_my_advert.php',['adverts'=>$adverts]);
//      неактивні оголошення
        $html_advert_disabled = $this->renderPartial('layout/_my_advert.php',['adverts'=>$adverts_disabled]);
//      уcі оголошення
        $html_advert_all = $this->renderPartial('layout/_my_advert.php',['adverts'=>$adverts_all]);

//      кількість активних оголошень для наступного виводу
        $advert_show = Advert::find()->where(['user'=>$advert['user'],'status'=>Advert::Active])->offset(5)->limit(5)->all();
        $advert_show = count($advert_show);
//      кількість усіх активних оголошень
        $advert_count = Advert::find()->where(['user'=>$user['id'],'status'=>Advert::Active])->all();
        $count_advert = count($advert_count);

//      кількість неактивних оголошень для наступного виводу
        $advert_show_disabled = Advert::find()->where(['user'=>$advert['user'],'status'=>Advert::Disabled])->offset(5)->limit(5)->all();
        $advert_show_disabled = count($advert_show_disabled);
//      кількість усіх неактивних оголошень
        $advert_count_disabled = Advert::find()->where(['user'=>$user['id'],'status'=>Advert::Disabled])->all();
        $count_advert_disabled = count($advert_count_disabled);
//      кількість усіх  оголошень для наступного виводу
        $advert_show_all = Advert::find()->where(['user'=>$advert['user']])->offset(5)->limit(5)->all();
        $advert_show_all = count($advert_show_all);
//      кількість усіх оголошень
        $advert_count_all = Advert::find()->where(['user'=>$user['id']])->all();
        $count_advert_all = count($advert_count_all);

        return $this->render('list-advert',compact('html_advert','advert_show','count_advert',
            'html_advert_disabled','advert_show_disabled','count_advert_disabled','html_advert_all','advert_show_all',
            'count_advert_all','sort_active','sort_disabled','sort_all'));
    }

    public function actionOtherAdvert(){

        $start = $_POST['start'];
        $status = $_POST['status'];

        $all_categories = Category::find()->all();
        $start_next = $start + 1;
        $start = ($start - 1) * 5;

        $advert_other = HelpFunction::SortFilter($status,$start);
        $start = ($start_next - 1)*5;
        $next = HelpFunction::SortFilter($status,$start);
        $count_other = count($next);

        $number_start_advert = $start_next;

        foreach ($advert_other as $item_other){
            HelpFunction::FormatAdvert($item_other,$all_categories);
        }

        $advert_other_html = $this->renderPartial('layout/_my_advert',['adverts'=>$advert_other]);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return json_encode([
            'advert_other' => $advert_other_html,
            'count' => $count_other,
            'start' => $number_start_advert
        ]);
    }


    public function actionShowAll(){

        $all_categories = Category::find()->all();

        $adverts = HelpFunction::SortFilter('all');

        foreach ($adverts as $item_other){
            HelpFunction::FormatAdvert($item_other,$all_categories);
        }

        $advert_all = $this->renderPartial('layout/_my_advert',['adverts'=>$adverts]);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return json_encode([
            'advert_other' => $advert_all,
        ]);

    }

    public function actionSort(){

        $property = $_POST['property'];
        $sort = $_POST['sort'];
        $type = $_POST['type_advert'];

        HelpFunction::SortSession($type,$property,$sort);

    }

}