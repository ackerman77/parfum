<?php
namespace app\controllers;

use app\models\Products;
use app\models\Orders;
use app\models\Users;
use Yii;
use yii\rest\Controller;

class OrdersController extends Controller
{
    public $modelClass = 'app\models\Orders';

    public function actionAdd($id_product)//Добавление в корзину
    {
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
        if ($user !== null) {
            $product = Products::findOne($id_product);
            if ($product !== null) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    $orders = new Orders();
                    $orders->product_id = $product->id_product;
                    $orders->user_id = $user->id_user;
                    $orders->save();

                    $transaction->commit();

                    $response = $this->response;
                    $response->data = [
                        'error' => [
                            'code' => 200,
                            'message' => 'Продукт добавлен в корзину',
                        ],
                    ];
                    return $response;
                } catch (\Exception $err) {
                    // Откат транзакции в случае ошибки
                    $transaction->rollBack();

                    Yii::error('Error booking car: ' . $err->getMessage(), 'app\controllers\ProductsController');

                    $response = $this->response;
                    $response->data = ['error' => 'Error products: ' . $err->getMessage()];
                    $response->statusCode = 500;
                    return $response;
                }
            } else {
                $response = $this->response;
                $response->statusCode = 404;
                $response->data = [
                    'error' => [
                        'code' => 404,
                        'message' => 'Product not found',
                    ],
                ];
                return $response;
            }
        } else {
            $response = $this->response;
            $response->statusCode = 401;
            $response->data = [
                'error' => [
                    'code' => 401,
                    'message' => 'Пользователь не зарегистрирован',
                ],
            ];
        }
    }
    public function actionDelete($order_id)//Удаление из корзины
    {
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
        if ($user !== null) {
            $order = Orders::findOne($order_id);
            if ($order !== null) {
                $order -> delete();
                $response = $this->response;
                $response->statusCode = 404;
                $response->data = [
                    'error' => [
                        'code' => 200,
                        'message' => 'Продукт успешно удален из корзины!',
                    ],
                ];
                return $response;
            } else {
                $response = $this->response;
                $response->statusCode = 404;
                $response->data = [
                    'error' => [
                        'code' => 404,
                        'message' => 'Order not found',
                    ],
                ];
                return $response;
            }
        } else {
            $response = $this->response;
            $response->statusCode = 401;
            $response->data = [
                'error' => [
                    'code' => 401,
                    'message' => 'Пользователь не зарегистрирован',
                ],
            ];
        }
    }

    
    private function findUserByToken($token)
    {
        return Users::findOne(['token' => $token]);
    }
}