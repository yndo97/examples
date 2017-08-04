<?php
/**
 * Created by PhpStorm.
 * User: gebruiker
 * Date: 25.07.17
 * Time: 14:04
 */
namespace frontend\models\fun;

use frontend\models\Advert;
use frontend\models\MessageUser;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use frontend\models\Advertising;
use Yii;

class HelpFunction extends Model {

    public function editImg($img,$index=null){
        if($index !== null) {
            $pic = json_decode($img);
            return $pic[$index];
        } else {
            $pic = json_decode($img);
            return $pic;
        }
    }

    public function formatPhone($phone){

        $phone = json_decode($phone);

        $phone = implode(',',$phone);

        return $phone;
    }


    public function AdvertisingService($service = null,$all_categories){

        $services = Advertising::findAll(['service'=>$service]);

        $services_advertising = [];

        foreach ($services as $item){
            $item_article = $item->advertItem;
            $item_article = HelpFunction::FormatAdvert($item_article,$all_categories);
            $services_advertising[] = $item_article;
        }

        return $services_advertising;
    }

    public function FormatAdvert($advert,$all_categories){

        $advert['url'] = $advert->UrlAdvert($all_categories);
        $advert['category'] = $advert->itemCategory;
        $advert['valuta'] = $advert->currency;
        $advert['created_at'] =  HelpFunction::rus_date('d.m.Y',strtotime($advert['created_at']));
        $advert->img = HelpFunction::editImg($advert['img']);
        $advert['user'] = $advert->userInfo;

        return $advert;
    }

    public function statusChecked($value,$property = null){

        if(isset($_GET[$value]) and $property == null){
            return 'checked';
        } elseif ((isset($_GET[$value]) and $_GET[$value] == $property)) {
            return 'checked';
        }
    }

    public function QueryFilter(){

        $condition = null;
        $photo = null;
        $time_order = null;
        $price_order = null;
        $valuta = null;

        if(isset($_GET['condition']) and $_GET['condition'] == 'new'){
            $condition = 1;
        } elseif(isset($_GET['condition']) and  $_GET['condition'] == 'used'){
            $condition = 2;
        }

        if(isset($_GET['photo'])){
            $photo = '["\/source\/default.jpg"]';
        }

        if(isset($_GET['time'])){
            $time_order = 'created_at DESC';
        }

        if((isset($_GET['price']) and $_GET['price'] == 'cheap')){
            $price_order = 'main_currency ASC';
        } elseif((isset($_GET['price']) and $_GET['price'] == 'expensive')){
            $price_order = 'main_currency DESC';
        }

        $query = Advert::find()
            ->where(['status'=>1])
            ->andFilterWhere(['condition'=>$condition])
            ->andFilterWhere(['!=','img',$photo])
            ->addOrderBy($time_order)
            ->addOrderBy($price_order);

        return $query;
    }

   public function rus_date() {
// Перевод
        $translate = array(
            "am" => "дп",
            "pm" => "пп",
            "AM" => "ДП",
            "PM" => "ПП",
            "Monday" => "Понедельник",
            "Mon" => "Пн",
            "Tuesday" => "Вторник",
            "Tue" => "Вт",
            "Wednesday" => "Среда",
            "Wed" => "Ср",
            "Thursday" => "Четверг",
            "Thu" => "Чт",
            "Friday" => "Пятница",
            "Fri" => "Пт",
            "Saturday" => "Суббота",
            "Sat" => "Сб",
            "Sunday" => "Воскресенье",
            "Sun" => "Вс",
            "January" => "Января",
            "Jan" => "Янв",
            "February" => "Февраля",
            "Feb" => "Фев",
            "March" => "Марта",
            "Mar" => "Мар",
            "April" => "Апреля",
            "Apr" => "Апр",
            "May" => "Мая",
            "May" => "Мая",
            "June" => "Июня",
            "Jun" => "Июн",
            "July" => "Июля",
            "Jul" => "Июл",
            "August" => "Августа",
            "Aug" => "Авг",
            "September" => "Сентября",
            "Sep" => "Сен",
            "October" => "Октября",
            "Oct" => "Окт",
            "November" => "Ноября",
            "Nov" => "Ноя",
            "December" => "Декабря",
            "Dec" => "Дек",
            "st" => "ое",
            "nd" => "ое",
            "rd" => "е",
            "th" => "ое"
        );
        // если передали дату, то переводим ее
        if (func_num_args() > 1) {
            $timestamp = func_get_arg(1);
            return strtr(date(func_get_arg(0), $timestamp), $translate);
        } else {
        // иначе текущую дату
            return strtr(date(func_get_arg(0)), $translate);
        }
   }

   public function formatServiceAdvertising($services){

       foreach ($service as $service_2){
           if(empty($service_2['time'])){
               $service_2['time'] = 'N';
           }
           $service_2['service'] = $service_2->itemService;
           $price_2 += $service_2['service']['price'];

       }

   }

    public function getLevel(&$categories, $id)
    {
        $level = 0;
        while( $id ){
            $index = null;
            foreach( $categories as $i => $row){
                if( $row['id'] == $id ){
                    $index = $i;
                    break;
                }
            }
            $id = null;
            if( $index !== null ){
                $id = $categories[$index]['sub'];
                if( $id ) $level++;
            }
        }
        return $level;
    }

    public function build_tree($cats,$parent_id,$level){

        if(is_array($cats) and isset($cats[$parent_id])){

            if($level <= 1) {
                $tree = '<ul class="dropdown_list">';
            }  else {
                $tree = '<ul class="dropdown_list_'.$level.'">';
            }

                foreach($cats[$parent_id] as $cat){

                    if($cat['next'] == 0){
                        $tree .= '<li data-dropdown-value="'.$cat['id'].'" class="dropdown_item">'.$cat['title'];
                    } elseif($cat['next'] == 1){
                        $tree .= '<li class="dropdown_item item_sub">'.$cat['title'];
                        $tree .= '<i class="icon-arrow"></i>';
                    }

                    $tree .= HelpFunction::build_tree($cats,$cat['id'],$cat['level']);
                    $tree .= '</li>';
                }

            $tree .= '</ul>';
        } else {
            return null;
        }
        return $tree;
    }

    public function TypeSort($sort,$type_advert){

        $session = Yii::$app->session;
        $session = $session['sort'];

        if(isset($session[$type_advert][$sort]) && $session[$type_advert][$sort] == 'desc'){
            $data_type = ['type'=>'asc','class'=>''];
        } elseif(isset($session[$type_advert][$sort]) && $session[$type_advert][$sort] == 'asc') {
            $data_type = ['type'=>'desc','class'=>'active'];
        } elseif(!isset($session[$type_advert][$sort])) {
            $data_type = ['type'=>'asc','class'=>''];
        }
        return $data_type;
    }

    public function SortFilter($status_advert = null,$offset = 0,$type){

        $name_sort = null;
        $price_sort = null;
        $status_sort = null;
        $message_sort = null;

        if($status_advert === 'all'){
            $status = ['status'=>null];
        } else {
            $status = ['status'=>$status_advert];
        }

        $user = Yii::$app->user->id;

        $session = Yii::$app->session;

        if(isset($session['sort'][$type])) {

            $session = $session['sort'][$type];

            if (isset($session['name'])) {
                $name_sort = 'title ' . $session['name'];
            }

            if (isset($session['price'])) {
                $price_sort = 'main_currency ' . $session['price'];
            }

            if (isset($session['date'])) {
                $status_sort = 'created_at ' . $session['date'];
            }

            if (isset($session['message'])) {
                $message_sort = 'count_message ' . $session['message'];
            }
        }

        $query = Advert::find()
            ->offset($offset)
            ->select(['advert.*','Count(message_user.advert) AS count_message'])
            ->join('LEFT JOIN', MessageUser::tableName(), 'advert.id=message_user.advert')
            ->groupBy('advert.id')
            ->where(['user'=>$user])
            ->andFilterWhere($status)
            ->addOrderBy($name_sort)
            ->addOrderBy($price_sort)
            ->addOrderBy($status_sort)
            ->addOrderBy($message_sort)
            ->limit(5)
            ->all();

        return $query;
    }

    public function SortSession($type,$property,$sort){

        Yii::$app->session->open();

        if(!isset($_SESSION['sort'][$type])){
            $_SESSION['sort'][$type] = [$property => $sort];

        } elseif(isset($_SESSION['sort'][$type][$property])) {
            $sort_new = $_SESSION['sort'][$type];
            $sort_new[$property] = $sort;
            $_SESSION['sort'][$type] = $sort_new;

        } elseif (!isset($_SESSION['sort'][$type][$property])){
            $new_property_sort =  [$property => $sort];
            $new_arr_sort = ArrayHelper::merge($_SESSION['sort'][$type],$new_property_sort);
            $_SESSION['sort'][$type] = $new_arr_sort;
        }
    }

}