<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id_user
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $phone
 * @property string $password
 * @property int $admin
 * @property string|null $token
 * @property Orders[] $orders
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() 
    { 
        return [ 
            [['email', 'password'], 'required'], 
            [['email'], 'email'], 
            [['email', 'password'], 'string', 'max' => 100], 
            [['first_name', 'last_name'], 'match', 'pattern' => '/^[\p{Cyrillic}A-Za-z\s]+$/u', 'message' => 'Пожалуйста, используйте только кириллицу или латиницу в имени и фамилии.'], 
            [['first_name', 'last_name'], 'string', 'max' => 50], 
            [['phone'], 'match', 'pattern' => '/^\+?\d{1,20}$/'], 
            [['token'], 'string', 'max' => 255], 
            [['admin'], 'integer'], 
            // Добавленная валидация пароля 
            ['password', 'validatePasswordStrength'], 
        ]; 
    }


    /**
     * {@inheritdoc}
     */
    public function validatePasswordStrength($attribute, $params) 
    { 
        $password = $this->$attribute; 
 
        // Проверка сложности пароля (примеры: минимальная длина, наличие букв, цифр и спец. символов) 
        if (strlen($password) < 8) { 
            $this->addError($attribute, 'Пароль должен содержать как минимум 8 символов.'); 
        } elseif (!preg_match('/\d/', $password)) { 
            $this->addError($attribute, 'Пароль должен содержать хотя бы одну цифру.'); 
        } elseif (!preg_match('/[A-Za-z]/', $password)) { 
            $this->addError($attribute, 'Пароль должен содержать хотя бы одну букву.'); 
        } elseif (!preg_match('/[\W_]/', $password)) { 
            $this->addError($attribute, 'Пароль должен содержать хотя бы один специальный символ.'); 
        } 
    } 
 
 
 
    public function register() 
    { 
        // Проверяем валидность модели перед сохранением 
        if ($this->validate()) { 
            // Проверяем наличие пользователя с таким же email в базе данных 
            $existingUser = Users::findOne(['email' => $this->email]); 
 
            if ($existingUser !== null) { 
                // Пользователь с таким email уже существует 
                $this->addError('email', 'Этот адрес электронной почты уже используется'); 
                return null; 
            } 
 
            $user = new Users(); 
            $user->first_name = $this->first_name; 
            $user->last_name = $this->last_name; 
            $user->email = $this->email; 
            $user->phone = $this->phone; 
            $user->password = Yii::$app->getSecurity()->generatePasswordHash($this->password); 
 
            // Генерация токена после успешной регистрации 
            $token = Yii::$app->security->generateRandomString(); 
            $user->token = $token; 
 
            if ($user->save()) { 
                return $token; 
            } 
        } 
 
        return null; 
    } 
 
    public function login() 
    { 
        if ($this->validate()) { 
            $user = Users::findOne(['email' => $this->email]); // Находим пользователя по указанному email
 
            if ($user === null) { 
                // Ошибка: Пользователь с указанным email не найден 
                $this->addError('email', 'Пользователь с таким email не найден'); 
                return null; 
            } 
 
            if (!Yii::$app->getSecurity()->validatePassword($this->password, $user->password)) { 
                // Ошибка: Неверный пароль 
                $this->addError('password', 'Неверный пароль'); 
                return null; 
            } 
 
            $token = Yii::$app->security->generateRandomString();//генерируем новый токен 
            $user->token = $token; //присвоение пользователю нового токена
 
            if (!$user->save()) { // Сохраняем изменения в базе данных
                // Ошибка обновления токена 
                return null; 
            } 
 
            return $token; 
        } 
 
        return null; 
    } 
 
    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id_user' => 'Id User', 
            'first_name' => 'First Name', 
            'last_name' => 'Last Name', 
            'email' => 'Email', 
            'phone' => 'Phone', 
            'password' => 'Password', 
            'token' => 'Token', 
        ]; 
    } 
 
    /** 
     * Gets query for [[Orders]]. 
     * 
     * @return \yii\db\ActiveQuery 
     */ 
    public function getOrders() 
    { 
        // Этот метод устанавливает связь между моделью Users и моделью Orders.
        // Он указывает, что у каждого пользователя может быть несколько заказов.
        return $this->hasMany(Orders::class, ['user_id' => 'id_user']); 
    }
}
