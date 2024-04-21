<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "announcements".
 *
 * @property int $id 公告ID
 * @property string $title 标题
 * @property string $content 内容
 * @property string $published_at 发布时间
 * @property string|null $updated_at 更新时间
 */
class Announcements extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'announcements';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'content', 'published_at'], 'required'],
            [['content'], 'string'],
            [['published_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => '公告ID',
            'title' => '标题',
            'content' => '内容',
            'published_at' => '发布时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * give me the latest 3 announcements
     * @return array
     */
    public static function fetchLatestAnnouncements(): array
    {
        return self::find()
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(3)
            ->all();
    }
}
