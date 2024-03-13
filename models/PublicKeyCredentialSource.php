<?php

namespace app\models;

use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\Exception\InvalidDataException;
use Yii;

/**
 * This is the model class for table "public_key_credential_source".
 *
 * @property int $id 记录id
 * @property int $user_id 对应的用户id
 * @property string $public_key_credential_source PKCS
 *
 * @property User $user
 */
class PublicKeyCredentialSource extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public_key_credential_source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'public_key_credential_source'], 'required'],
            [['user_id'], 'integer'],
            [['public_key_credential_source'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'public_key_credential_source' => 'Public Key Credential Source',
        ];
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

    /**
     * 获取用户相关的所有公钥凭证
     * @param User $user
     * @return \Webauthn\PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(User $user): array
    {
        $records = self::findAll(['user_id' => $user->id]);
        $publicKeyCredentialSources = [];
        $webauthnSerializerFactory = new WebauthnSerializerFactory(new AttestationStatementSupportManager());
        foreach ($records as $record) {
            $publicKeyCredentialSource = $webauthnSerializerFactory->create()->deserialize($record->public_key_credential_source, \Webauthn\PublicKeyCredentialSource::class, 'json');
            $publicKeyCredentialSources[] = $publicKeyCredentialSource;
        }
        return $publicKeyCredentialSources;
    }

    /**
     * 从数据库中获取指定$id的记录，转为PublicKeyCredentialSource对象
     * @param int $id
     * @return \Webauthn\PublicKeyCredentialSource|null
     */
    public function findOneByCredentialId(int $id): ?\Webauthn\PublicKeyCredentialSource
    {
        $record = self::findOne(['id' => $id]);
        if ($record === null) {
            return null;
        }
        $webauthnSerializerFactory = new WebauthnSerializerFactory(new AttestationStatementSupportManager());
        return $webauthnSerializerFactory->create()->deserialize($record->public_key_credential_source, \Webauthn\PublicKeyCredentialSource::class, 'json');
    }

    /**
     * 保存PublicKeyCredentialSource对象到数据库
     * @param \Webauthn\PublicKeyCredentialSource $PKCS
     * @return void
     */
    public function saveCredential(\Webauthn\PublicKeyCredentialSource $PKCS): void
    {
        // Create an instance of WebauthnSerializerFactory
        $webauthnSerializerFactory = new WebauthnSerializerFactory(new AttestationStatementSupportManager());

        // Serialize the PublicKeyCredentialSource object into a JSON string
        $publicKeyCredentialSourceJson = $webauthnSerializerFactory->create()->serialize($PKCS, 'json');

        // Save the JSON string to the public_key_credential_source field in the database
        $this->public_key_credential_source = $publicKeyCredentialSourceJson;
        $this->user_id = Yii::$app->user->id;
        // Save the record to the database
        $this->save();
    }
}
