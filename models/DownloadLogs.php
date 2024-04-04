<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "download_logs".
 *
 * @property int $id 日志id
 * @property int|null $user_id 用户id,未登录时NULL
 * @property int|null $share_id 分享id
 * @property string|null $access_time 访问时间
 * @property string|null $ip_address ip地址
 * @property string|null $user_agent UA
 *
 * @property Share $share
 * @property User $user
 */
class DownloadLogs extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'download_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'share_id'], 'integer'],
            [['access_time'], 'safe'],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['share_id'], 'exist', 'skipOnError' => true, 'targetClass' => Share::class, 'targetAttribute' => ['share_id' => 'share_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => '日志id',
            'user_id' => '用户id,未登录时NULL',
            'share_id' => '分享id',
            'access_time' => '访问时间',
            'ip_address' => 'ip地址',
            'user_agent' => 'UA',
        ];
    }

    /**
     * Gets query for [[Share]].
     *
     * @return ActiveQuery
     */
    public function getShare(): ActiveQuery
    {
        return $this->hasOne(Share::class, ['share_id' => 'share_id']);
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
     * @param int $share_id
     * @param string $ipAddress
     * @param string $userAgent
     * @return void
     */
    public static function addLog(int|null $userId, int $share_id, string $ipAddress, string $userAgent): void
    {
        $log = new self();
        $log->user_id = $userId??null;
        $log->share_id = $share_id;
        $log->ip_address = $ipAddress;
        $log->access_time = date('Y-m-d H:i:s'); // 使用当前时间作为登录时间
        $log->user_agent = strlen($userAgent) > 250 ? substr($userAgent, 0, 250) : $userAgent;

        $log->save();
    }
}
