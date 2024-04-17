<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tickets".
 *
 * @property int $id 工单id
 * @property int $user_id 发起工单的用户id
 * @property string $title 工单标题
 * @property string $description 工单内容
 * @property int $status 工单状态 // 0:工单已开启 1:管理员已回复 2:用户已回复 3:工单已关闭
 * @property string $created_at 工单创建时间
 * @property string $updated_at 工单更新时间
 * @property string $ip ip地址
 *
 * @property TicketReplies[] $ticketReplies
 * @property User $user
 */
class Tickets extends ActiveRecord
{
    const STATUS_OPEN = 0;
    const STATUS_ADMIN_REPLY = 1;
    const STATUS_USER_REPLY = 2;
    const STATUS_CLOSED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'tickets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'title', 'status','ip'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 50],
            [['ip'], 'string', 'max' => 150],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => '工单id',
            'user_id' => '发起工单的用户id',
            'title' => '工单标题',
            'description' => '工单内容',
            'status' => '工单状态',
            'created_at' => '工单创建时间',
            'updated_at' => '工单更新时间',
            'ip' => 'ip地址',
        ];
    }

    /**
     * Gets query for [[TicketReplies]].
     *
     * @return ActiveQuery
     */
    public function getTicketReplies(): ActiveQuery
    {
        return $this->hasMany(TicketReplies::class, ['ticket_id' => 'id']);
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
}
