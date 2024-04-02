<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "login_logs".
 *
 * @property int $id 记录id
 * @property int|null $user_id 用户id
 * @property string|null $ip_address ip地址
 * @property string $login_time 登录时间
 * @property string|null $user_agent UA
 * @property int|null $status 登录状态(0 FAIL 1 SUCCESS)
 *
 * @property User $user
 */
class LoginLogs extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'login_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['login_time'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['login_time'], 'safe'],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'ip_address' => 'Ip Address',
            'login_time' => 'Login Time',
            'user_agent' => 'User Agent',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param int|null $userId
     * @param string $ipAddress
     * @param string $userAgent
     * @param int $status
     * @return void
     */
    public static function addLog(int|null $userId, string $ipAddress, string $userAgent, int $status): void
    {
        $log = new self();
        $log->user_id = $userId??null;
        $log->ip_address = $ipAddress;
        $log->login_time = date('Y-m-d H:i:s'); // 使用当前时间作为登录时间
        $log->user_agent = strlen($userAgent) > 250 ? substr($userAgent, 0, 250) : $userAgent;
        $log->status = $status;

        $log->save();
    }
}
