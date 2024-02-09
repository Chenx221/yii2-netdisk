<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id 用户ID
 * @property string|null $username 用户名
 * @property string|null $password 密码
 * @property string|null $auth_key authkey
 * @property string|null $email 邮箱
 * @property int|null $status 用户状态
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    public $password2; // 重复密码
    public $rememberMe; // 记住我

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * // rules说明
     * // 1. username, password, password2, email 必填
     * // 2. username 长度在3-12之间
     * // 3. password 长度在6-12之间
     * // 4. password2 必须和password一致
     * // 5. email 必须是邮箱格式
     * // 6. username, email 必须是唯一的
     * *
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'password2'], 'required', 'on' => 'login'],
            [['username', 'password', 'email', 'password2'], 'required', 'on' => 'register'],
            ['username', 'string', 'min' => 3, 'max' => 12],
            ['password', 'string', 'min' => 6, 'max' => 12],
            ['password2', 'compare', 'compareAttribute' => 'password', 'on' => 'register'],
            ['email', 'email', 'on' => 'register'],
            ['username', 'unique', 'on' => 'register'],
            ['email', 'unique', 'on' => 'register'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'email' => 'Email',
            'status' => 'Status',
        ];
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // This method is not needed if you don't use access tokens for authentication.
        return null;
    }

    /**
     * Returns the ID of the user.
     *
     * @return string|int the ID of the user
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns an auth key used to authenticate cookie-based login.
     *
     * @return string the auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     *
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 用户登录处理
     *
     * @return bool 返回用户名密码验证状态
     */
    public function login()
    {
        $user = User::findOne(['username' => $this->username]);

        if ($user !== null && $user->validatePassword($this->password)) {
            // check user status
            if ($user->status == 0) {
                $this->addError('username', '此用户已被禁用,请联系管理员获取支持');
                return false;
            }

            $rememberMe = $this->rememberMe ? 3600 * 24 * 30 : 0;
            return Yii::$app->user->login($user, $rememberMe);
        }

        return false;
    }

    /**
     * 验证密码
     *
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }
}