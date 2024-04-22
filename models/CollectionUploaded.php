<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "collection_uploaded".
 *
 * @property int $id 文件收集的上传记录id
 * @property int|null $user_id 用户ID
 * @property int $task_id 对应的文件收集id
 * @property string $uploader_ip 上传者ip
 * @property string $uploaded_at 上传时间
 * @property string $subfolder_name 对应的子文件夹名
 * @property string $user_agent 浏览器UA信息
 *
 * @property CollectionTasks $task
 * @property User $user
 */
class CollectionUploaded extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'collection_uploaded';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['task_id', 'uploader_ip', 'subfolder_name', 'user_agent'], 'required'],
            [['user_id', 'task_id'], 'integer'],
            [['uploaded_at'], 'safe'],
            [['uploader_ip'], 'string', 'max' => 45],
            [['subfolder_name'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => CollectionTasks::class, 'targetAttribute' => ['task_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => '上传记录id',
            'user_id' => '用户ID',
            'task_id' => '收集任务id',
            'uploader_ip' => '上传者ip',
            'uploaded_at' => '上传时间',
            'subfolder_name' => '所在位置',
            'user_agent' => '浏览器UA信息',
        ];
    }

    /**
     * Gets query for [[Task]].
     *
     * @return ActiveQuery
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(CollectionTasks::class, ['id' => 'task_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
