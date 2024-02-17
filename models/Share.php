<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "share".
 *
 * @property int $share_id 分享ID
 * @property int $sharer_id 分享者ID
 * @property string $file_relative_path 文件的相对路径
 * @property string $access_code 分享密钥
 * @property string $creation_date 分享创建日期
 *
 * @property User $sharer
 */
class Share extends \yii\db\ActiveRecord
{
    const SCENARIO_UPDATE = 'update';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'share';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_relative_path', 'access_code'], 'required'],
            [['sharer_id'], 'integer'],
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
    public function attributeLabels()
    {
        return [
            'share_id' => '分享ID',
            'sharer_id' => '分享者ID',
            'file_relative_path' => '文件位置',
            'access_code' => '访问密码',
            'creation_date' => '分享创建日期',
        ];
    }

    /**
     * Gets query for [[Sharer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSharer()
    {
        return $this->hasOne(User::class, ['id' => 'sharer_id']);
    }
    public function getSharerUsername()
    {
        return $this->sharer->username;
    }
}
