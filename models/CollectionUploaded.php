<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "collection_uploaded".
 *
 * @property int $id 文件收集的上传记录id
 * @property int $task_id 对应的文件收集id
 * @property string $uploader_ip 上传者ip
 * @property string $uploaded_at 上传时间
 * @property string $subfolder_name 对应的子文件夹名
 *
 * @property CollectionTasks $task
 */
class CollectionUploaded extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_uploaded';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'uploader_ip', 'subfolder_name'], 'required'],
            [['task_id'], 'integer'],
            [['uploaded_at'], 'safe'],
            [['uploader_ip'], 'string', 'max' => 45],
            [['subfolder_name'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => CollectionTasks::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '上传记录id',
            'task_id' => '收集任务id',
            'uploader_ip' => '上传者ip',
            'uploaded_at' => '上传时间',
            'subfolder_name' => '所在位置',
        ];
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(CollectionTasks::class, ['id' => 'task_id']);
    }
}
