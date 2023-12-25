<?php
namespace app\controllers;

use app\models\Users;
use Yii;
use app\models\Orders;
use yii\rest\Controller;

class UserController extends Controller
{
    public $modelClass = 'app\models\Users';//импортируем в контроллер таблицу users

    public function actionCreate()//запрос регистрации
    {
        $model = new Users();
        $model->load(Yii::$app->request->post(), '');

        $registeredUser = $model->register();

        if ($registeredUser !== null) {
            $response = $this->response;
            $response->statusCode = 200; // Успешная регистрация пользователя
            return $response;
        } else {
            // Обработка специфических ошибок
            if ($model->hasErrors()) {
                // Ошибка валидации данных пользователя
                $response = $this->response;
                $response->statusCode = 422;
                $response->data = [
                    'error' => [
                        'code' => 422,
                        'message' => 'Validation error',
                        'errors' => $model->getErrors(), // Детали ошибок валидации
                    ],
                ];
                return $response;
            } else {
                // Ошибка конфликта почты
                $response = $this->response;
                $response->statusCode = 409;
                $response->data = [
                    'error' => [
                        'code' => 409,
                        'message' => 'Этот адрес электронной почты уже используется',
                    ],
                ];
                return $response;
            }
        }
    }

    public function actionLogin()//авториация
    {
        $model = new Users();
        $model->load(Yii::$app->request->post(), '');

        $loggedInUser = $model->login();

        if ($loggedInUser !== null) {
            $response = $this->response;
            $response->statusCode = 200;
            $response->data = [
                'message' => 'Успешный вход в аккаунт',
                'token' => $loggedInUser,
            ];
            return $response;
        } else {
            $response = $this->response;

            if ($model->hasErrors()) {
                // Ошибка валидации данных при входе
                $response->statusCode = 422;
                $response->data = [
                    'error' => [
                        'code' => 422,
                        'message' => 'Ошибка входа: неверные данные',
                        'errors' => $model->getErrors(),
                    ],
                ];
            } else {
                // Ошибка аутентификации
                $response->statusCode = 401;
                $response->data = [
                    'error' => [
                        'code' => 401,
                        'message' => 'Неверный email или пароль',
                    ],
                ];
            }

            return $response;
        }
    }

    public function actionUser($id_user)//Получение данных пользователя
    {
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
        $find_user = Users::findOne($id_user);

        // Проверка прав пользователя
        if ($user !== null && $user->admin !== 0) {
            if ($find_user !== null) {
                $response = $this->response;
                $response->statusCode = 200;
                $response->data = [
                    'user' => $find_user, // Вернуть найденного пользователя
                ];
                return $response;
            } else {
                $response = $this->response;
                $response->statusCode = 404;
                $response->data = [
                    'error' => [
                        'code' => 404,
                        'message' => 'User not found',
                    ],
                ];
                return $response;
            }
        } else {
            $response = $this->response;
            $response->statusCode = 403;
            $response->data = [
                'error' => [
                    'code' => 403,
                    'message' => 'Отсутствуют права администратора или пользователь не авторизован',
                ],
            ];
            return $response;
        }
    }

    public function actionUsers()//Получения данных всех пользователей
    {
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
        $find_user = Users::find()->all();
        if ($user !== null && $user->admin !== 0) {
            if ($find_user !== null) {
                $response = $this->response;
                $response->statusCode = 200;
                $response->data = $find_user;
            } else {
                $response = $this->response;
                $response->statusCode = 404;
                $response->data = [
                    'error' => [
                        'code' => 404,
                        'message' => 'Users not found',
                    ],
                ];
                return $response;
            }
        } else {
            $response = $this->response;
            $response->statusCode = 404;
            $response->data = [
                'error' => [
                    'code' => 403,
                    'message' => 'Отсутствуют права администратора или пользователь не авторизован',
                ],
            ];
            return $response;
        }
    }

    public function actionOrders()
{
    $token = str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization'));
    $user = $this->findUserByToken($token);

    if ($user !== null) {
        $orders = $user->orders;

        $response = $this->response;
        $response->statusCode = 200;
        $response->data = [
            'orders' => $orders,
        ];
        return $response;
    } else {
        $response = $this->response;
        $response->statusCode = 401;
        $response->data = [
            'error' => [
                'code' => 401,
                'message' => 'Вы не зарегистрированы',
            ],
        ];
        return $response;
    }
}



    // public function actionOrders($id_user)//Просмотр корзины
    // {
    //     $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
    //     if ($user !== null) {
    //         $orders = Orders::find()
    //             ->where(['user_id' => $id_user])
    //             ->all();

    //         if (!empty($orders)) {
    //             $response = $this->response;
    //             $response->statusCode = 200;
    //             $response->data = $orders;
    //         } else {
    //             $response = $this->response;
    //             $response->statusCode = 404;
    //             $response->data = [
    //                 'error' => [
    //                     'code' => 404,
    //                     'message' => 'Корзина пуста',
    //                 ],
    //             ];
    //         }
    //     } else {
    //         $response = $this->response;
    //         $response->statusCode = 404;
    //         $response->data = [
    //             'error' => [
    //                 'code' => 404,
    //                 'message' => 'Незарегистрированный пользователь',
    //             ],
    //         ];
    //     }

    //     return $response;
    // }


    private function findUserByToken($token)
    {
        return Users::findOne(['token' => $token]);
    }

}

