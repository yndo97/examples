<?php
namespace frontend\components;

use frontend\models\OrderProduct;
use frontend\models\Order;
use yii\base\Component;
use Yii;
use frontend\models\User;

/**
 * Class Cart
 * @package frontend\components
 *
 * @property Order $order
 * @property string $status
 */
class Cart extends Component
{
    const SESSION_KEY = 'order_id';

    private $_order;

    public function add($productId, $count,$price)
    {

        $link = new OrderProduct();
        $link->product_id = $productId;
        $link->order_id = $this->order->id;
        $link->count = $count;
        $link->price = $price;
        return $link->save();
    }

    public function getOrder()
    {
        if ($this->_order == null) {
            $this->_order = Order::findOne(['id' => $this->getOrderId()]);
        }
        return $this->_order;
    }

    private function getOrderId()
    {
        if (!Yii::$app->session->has(self::SESSION_KEY)) {
            if ($this->createOrder()) {
                Yii::$app->session->set(self::SESSION_KEY, $this->_order->id);
            }
        }
        return Yii::$app->session->get(self::SESSION_KEY);
    }

    public function delete($productId)
    {
        $link = OrderProduct::findOne(['product_id' => $productId, 'order_id' => $this->getOrderId()]);
        if (!$link) {
            return false;
        }
        return $link->delete();
    }

    public function setCount($productId, $count)
    {
        $link = OrderProduct::findOne(['product_id' => $productId, 'order_id' => $this->getOrderId()]);
        if (!$link) {
            return false;
        }
        $link->count = $count;
        return $link->save();
    }

    public function isEmpty()
    {
        if (!Yii::$app->session->has(self::SESSION_KEY)) {
            return true;
        }
        return $this->order->productsCount ? false : true;
    }

    public function createOrder()
    {
        $order = new Order();
        $order->status = 0;
        if ($order->save()) {
            $this->_order = $order;
            return true;
        }
        return false;
    }

}