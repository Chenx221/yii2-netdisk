<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id 用户ID
 * @property string|null $username 用户名
 * @property string|null $name 昵称
 * @property string|null $password 密码
 * @property string|null $auth_key authkey
 * @property string|null $email 邮箱
 * @property int|null $status 账户是否启用
 * @property string|null $created_at 账户创建时间
 * @property string|null $last_login 上次登陆时间
 * @property string|null $last_login_ip 上次登录ip
 * @property string|null $bio 备注
 * @property string|null $role 身份
 * @property string|null $encryption_key 加密密钥
 * @property string|null $otp_secret otp密钥
 * @property int|null $is_encryption_enabled 启用加密
 * @property int|null $is_otp_enabled 启用otp
 * @property int|null $storage_limit 存储容量限制,MB
 * @property string|null $recovery_codes OTP恢复代码
 * @property int|null $dark_mode 夜间模式(0 off,1 on,2 auto)
 *
 * @property CollectionTasks[] $collectionTasks
 * @property Share[] $shares
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $password2; // 重复密码
    public $rememberMe; // 记住我

    public $oldPassword; // 旧密码 修改密码用
    public $newPassword; // 新密码 修改密码用
    public $newPasswordRepeat; // 重复新密码 修改密码用
    public $totp_input; // otp用户输入值
    public $recoveryCode_input; // 恢复代码用户输入

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status', 'is_encryption_enabled', 'is_otp_enabled','dark_mode'], 'integer'],
            [['created_at', 'last_login'], 'safe'],
            [['bio', 'totp_input','recoveryCode_input','name'], 'string'],
            [['encryption_key', 'otp_secret', 'recovery_codes'], 'string', 'max' => 255],
            [['last_login_ip'], 'string', 'max' => 45],
            [['username', 'password'], 'required', 'on' => 'login'],
            [['username', 'password', 'email', 'password2'], 'required', 'on' => 'register'],
            ['username', 'string', 'min' => 3, 'max' => 12],
            ['password', 'string', 'min' => 6, 'max' => 24, 'on' => 'register'],
            ['password2', 'compare', 'compareAttribute' => 'password', 'on' => 'register'],
            ['email', 'email', 'on' => 'register'],
            ['username', 'unique', 'on' => 'register'],
            ['email', 'unique', 'on' => 'register'],
            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'required', 'on' => 'changePassword'],
            ['oldPassword', 'validatePassword2', 'on' => 'changePassword'],
            ['newPassword', 'string', 'min' => 6, 'max' => 24, 'on' => 'changePassword'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword', 'on' => 'changePassword'],
            ['newPassword', 'compare', 'compareAttribute' => 'oldPassword', 'operator' => '!=', 'message' => '新密码不能与旧密码相同', 'on' => 'changePassword'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @return void
     */
    public function validatePassword2($attribute, $params): void
    {
        if (!$this->hasErrors()) {
            if (!Yii::$app->security->validatePassword($this->$attribute, $this->password)) {
                $this->addError($attribute, '原密码不匹配');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'name' => 'Name',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'last_login' => 'Last Login',
            'last_login_ip' => 'Last Login Ip',
            'bio' => 'Bio',
            'role' => 'Role',
            'encryption_key' => 'Encryption Key',
            'otp_secret' => 'Otp Secret',
            'is_encryption_enabled' => 'Is Encryption Enabled',
            'is_otp_enabled' => 'Is Otp Enabled',
            'storage_limit' => 'Storage Limit',
            'recovery_codes' => 'Recovery Codes',
            'dark_mode' =>'Dark Mode'
        ];
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id): ?IdentityInterface
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
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        // This method is not needed if you don't use access tokens for authentication.
        return null;
    }

    /**
     * Returns the ID of the user.
     *
     * @return int the ID of the user
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns an auth key used to authenticate cookie-based login.
     *
     * @return string|null the auth key
     */
    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     *
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 用户登录处理
     *
     * @return bool 返回用户名密码验证状态
     */
    public function login(): bool
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
    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Gets query for [[CollectionTasks]].
     *
     * @return ActiveQuery
     */
    public function getCollectionTasks(): ActiveQuery
    {
        return $this->hasMany(CollectionTasks::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Shares]].
     *
     * @return ActiveQuery
     */
    public function getShares(): ActiveQuery
    {
        return $this->hasMany(Share::class, ['sharer_id' => 'id']);
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     * 获取Gravatar头像url或完整的img标签
     *
     * @param string $email The email address
     * @param int|string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boolean $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source https://gravatar.com/site/implement/images/php/
     */
    public function getGravatar(string $email, int|string $s = 80, string $d = 'mp', string $r = 'x', bool $img = false, array $atts = array()): string
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val)
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

    public function deleteAccount(): false|int
    {
        // 设置用户状态为禁用
        $this->status = 0;

        // 保存用户模型
        if (!$this->save()) {
            return false; // something wrong
        }
        // 更新与用户相关的所有 CollectionTasks 和 Share 记录的状态为禁用
        CollectionTasks::updateAll(['status' => 0], ['user_id' => $this->id]);
        Share::updateAll(['status' => 0], ['sharer_id' => $this->id]);

        return true;
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        $auth = Yii::$app->authManager;
        $role = $auth->getRole($this->role);
        if ($role) {
            if (!$insert) {
                $auth->revokeAll($this->id);
            }
            $auth->assign($role, $this->id);
        }
    }
}