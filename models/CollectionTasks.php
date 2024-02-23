<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "collection_tasks".
 *
 * @property int $id 收集任务id
 * @property int $user_id 用户id
 * @property string $folder_path 收集目标文件夹(相对路径)
 * @property string $created_at 收集任务创建时间
 * @property string $secret 访问密钥
 *
 * @property CollectionUploaded[] $collectionUploadeds
 * @property User $user
 */
class CollectionTasks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'folder_path', 'secret'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['folder_path', 'secret'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '收集任务id',
            'user_id' => '用户id',
            'folder_path' => '收集目标文件夹(相对路径)',
            'created_at' => '任务创建时间',
            'secret' => '访问密钥',
        ];
    }

    /**
     * Gets query for [[CollectionUploadeds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionUploadeds()
    {
        return $this->hasMany(CollectionUploaded::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
