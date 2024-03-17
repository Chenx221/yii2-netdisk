<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "share".
 *
 * @property int $share_id 分享ID
 * @property int $sharer_id 分享者ID
 * @property string $file_relative_path 文件的相对路径
 * @property string $access_code 分享密钥
 * @property string $creation_date 分享创建日期
 * @property int|null $status 分享是否启用
 *
 * @property User $sharer
 */
class Share extends ActiveRecord
{
    const string SCENARIO_UPDATE = 'update';
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'share';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['file_relative_path', 'access_code'], 'required'],
            [['sharer_id', 'status'], 'integer'],
            [['creation_date'], 'safe'],
            [['file_relative_path'], 'string', 'max' => 255],
            [['access_code'], 'string', 'max' => 4],
            [['sharer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sharer_id' => 'id']],
            [['access_code'], 'required', 'on' => self::SCENARIO_UPDATE],  // 在 'update' 场景中，只验证 'access_code' 字段
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'share_id' => '分享ID',
            'sharer_id' => '分享者ID',
            'file_relative_path' => '文件位置',
            'access_code' => '访问密码',
            'creation_date' => '分享创建日期',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[Sharer]].
     *
     * @return ActiveQuery
     */
    public function getSharer(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'sharer_id']);
    }
    public function getSharerUsername(): ?string
    {
        return $this->sharer->username;
    }
}
