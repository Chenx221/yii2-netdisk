<?php

namespace app\models;

use JsonException;
use Webauthn\PublicKeyCredentialSource;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "public_key_credential_source_repository".
 *
 * @property int $id 记录id
 * @property int $user_id 对应的用户id
 * @property string $name 标识名
 * @property string $public_key_credential_id PKC_ID
 * @property string $data PKC_DATA
 *
 * @property User $user
 */
class PublicKeyCredentialSourceRepository extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'public_key_credential_source_repository';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'name', 'public_key_credential_id', 'data'], 'required'],
            [['user_id'], 'integer'],
            [['data'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['public_key_credential_id'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'public_key_credential_id' => 'Public Key Credential ID',
            'data' => 'Data',
        ];
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
     * 获取用户相关的所有公钥凭证对象
     * @param User $user
     * @return array
     */
    public function findAllForUserEntity(User $user): array
    {
        return self::findAll(['user_id' => $user->id]);
    }

    /**
     * 从数据库中获取指定$id的记录对象
     * @param string $PKC_ID
     * @return PublicKeyCredentialSourceRepository|null
     */
    public function findOneByCredentialId(string $PKC_ID): ?PublicKeyCredentialSourceRepository
    {
        return self::findOne(['public_key_credential_id' => $PKC_ID]);
    }

    /**
     * 保存PublicKeyCredentialSource对象到数据库
     * @param PublicKeyCredentialSource $PKCS
     * @param string $name
     * @param bool $isNewRecord
     * @return bool
     * @throws JsonException
     */
    public function saveCredential(PublicKeyCredentialSource $PKCS, string $name, bool $isNewRecord = true): bool
    {
        $jsonSerialize = $PKCS->jsonSerialize();
        $this->public_key_credential_id = $jsonSerialize['publicKeyCredentialId'];
        $publicKeyCredentialSourceJson = json_encode($jsonSerialize, JSON_THROW_ON_ERROR);
        $this->data = $publicKeyCredentialSourceJson;
        if($isNewRecord){
            $this->name = $name;
        }
        $this->user_id = Yii::$app->user->id;
        return $this->save();
    }
}
