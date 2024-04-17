<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ticket_replies".
 *
 * @property int $id 工单消息id
 * @property int $ticket_id 归属的工单id
 * @property int $user_id 消息发送者id
 * @property string $message 消息内容
 * @property string $created_at 发送时间
 * @property string $ip ip地址
 *
 * @property Tickets $ticket
 * @property User $user
 */
class TicketReplies extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ticket_replies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ticket_id', 'user_id', 'message', 'ip'], 'required'],
            [['ticket_id', 'user_id'], 'integer'],
            [['message'], 'string'],
            [['created_at'], 'safe'],
            [['ip'], 'string', 'max' => 150],
            [['ticket_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tickets::class, 'targetAttribute' => ['ticket_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => '工单消息id',
            'ticket_id' => '归属的工单id',
            'user_id' => '消息发送者id',
            'message' => '消息内容',
            'created_at' => '发送时间',
            'ip' => 'ip地址',
        ];
    }

    /**
     * Gets query for [[Ticket]].
     *
     * @return ActiveQuery
     */
    public function getTicket(): ActiveQuery
    {
        return $this->hasOne(Tickets::class, ['id' => 'ticket_id']);
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