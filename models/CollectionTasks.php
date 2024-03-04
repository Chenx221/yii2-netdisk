<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "collection_tasks".
 *
 * @property int $id 收集任务id
 * @property int $user_id 用户id
 * @property string $folder_path 收集目标文件夹(相对路径)
 * @property string $created_at 收集任务创建时间
 * @property string $secret 访问密钥
 * @property int|null $status 收集任务是否启用
 *
 * @property CollectionUploaded[] $collectionUploadeds
 * @property User $user
 */
class CollectionTasks extends ActiveRecord
{
    const SCENARIO_CREATE = 'create';

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
            [['folder_path', 'secret'], 'required', 'on' => self::SCENARIO_CREATE],
            [['user_id', 'status'], 'integer'],
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
            'status' => 'Status',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['folder_path', 'secret']; // 在这里列出你想在创建收集任务时验证的属性
        return $scenarios;
    }

    /**
     * Gets query for [[CollectionUploadeds]].
     *
     * @return ActiveQuery
     */
    public function getCollectionUploadeds()
    {
        return $this->hasMany(CollectionUploaded::class, ['task_id' => 'id']);
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
