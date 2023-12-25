<?php
namespace app\controllers;

use app\models\Products;
use app\models\Users;
use Yii;
use yii\web\UploadedFile;
use yii\rest\Controller;

class ProductsController extends Controller
{
    public $modelClass = 'app\models\Products';
    public function actionProducts()//Получить список всех продуктов
    {
        $products = Products::find()->all();
        if ($products !== null) {
            $response = $this->response;
            $response->statusCode = 200;
            $response->data = $products;
        } else {
            $response = $this->response;
            $response->statusCode = 404;
            $response->data = [
                'error' => [
                    'code' => 404,
                    'message' => 'No products found in the system',
                ],
            ];
            return $response;
        }
    }

    public function actionProduct($id_product)//Получить один продукт
    {
        $product = Products::findOne($id_product);
        if ($product !== null) {
            $response = $this->response;
            $response->statusCode = 200;
            $response->data = [
                $product,
            ];
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
    }
    
    public function actionAdd()//Добавление продукта в систему
    {
        $data = Yii::$app->request->getBodyParams();
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
    
        if ($user !== null && $user->admin !== 0) {
            $product = new Products();
            $product->load($data, '');
            
            $product->image = UploadedFile::getInstanceByName('image');
    
            if ($product->image) {
                $imageName = 'product_' . time() . '.' . $product->image->extension;
                $imagePath = Yii::getAlias('@app/api/uploads/') . $imageName;

                if (!is_dir(Yii::getAlias('@app/api/uploads/'))) {
                    mkdir(Yii::getAlias('@app/api/uploads/'), 0777, true); // Создаем папку, если ее нет
                }

                if (copy($product->image->tempName, $imagePath)) {
                    $product->image = 'uploads/' . $imageName;
                } else {
                    $response = $this->response;
                    $response->statusCode = 400;
                    $response->data = [
                        'error' => [
                            'code' => 400,
                            'message' => 'Ошибка сохранения изображения',
                        ],
                    ];
                    return $response;
                }
            }
            if ($product->validate()) {
    
                if ($product->save()) {
                    $response = $this->response;
                    $response->statusCode = 200;
                    $response->data = [$product];
                    return $response;
                } else {
                    $response = $this->response;
                    $response->statusCode = 400;
                    $response->data = [
                        'error' => [
                            'code' => 400,
                            'message' => 'Ошибка сохранения продукта',
                            'errors' => $product->getErrors(),
                        ],
                    ];
                    return $response;
                }
            } else {
                $response = $this->response;
                $response->statusCode = 422;
                $response->data = [
                    'error' => [
                        'code' => 422,
                        'message' => 'Ошибка валидации данных для создания продукта',
                        'errors' => $product->getErrors(),
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
                    'message' => 'Отсутствуют права администратора',
                ],
            ];
            return $response;
        }
    }

    public function actionDelete($id_product)//Удаление продукта из системы
    {
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
        if ($user !== null && $user->admin !== 0) {
            $product = Products::findOne($id_product);
            if ($product !== null) {
                $product->delete();
                $response = $this->response;
                $response->statusCode = 200;
                $response->data = [
                    'error' => [
                        'code' => 200,
                        'message' => 'Продукт успешно удален!',
                    ],
                ];
                return $response;
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
            $response->statusCode = 403;
            $response->data = [
                'error' => [
                    'code' => 403,
                    'message' => 'Отсутствуют права администратора',
                ],
            ];
        }
    }

    public function actionUpdate($id_product)//Изменение данных о продукте
    {
        $user = $this->findUserByToken(str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization')));
        if ($user !== null && $user->admin !== 0) {
            $product = Products::findOne($id_product);
            if ($product !== null) {
                ;
                $product->load(Yii::$app->request->post(), '');
                $product->save();

                $response = $this->response;
                $response->statusCode = 404;
                $response->data = [
                    'error' => [
                        'code' => 200,
                        'message' => 'Продукт успешно изменен!',
                    ],
                ];
                return $response;
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
            $response->statusCode = 403;
            $response->data = [
                'error' => [
                    'code' => 403,
                    'message' => 'Отсутствуют права администратора',
                ],
            ];
        }
    }

    private function findUserByToken($token)
    {
        return Users::findOne(['token' => $token]);
    }
}