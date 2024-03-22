<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'status', 'is_encryption_enabled', 'is_otp_enabled', 'storage_limit', 'dark_mode'], 'integer'],
            [['username', 'name', 'password', 'auth_key', 'email', 'created_at', 'last_login', 'last_login_ip', 'bio', 'role', 'encryption_key', 'otp_secret', 'recovery_codes', 'vault_secret', 'vault_salt'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = User::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'last_login' => $this->last_login,
            'is_encryption_enabled' => $this->is_encryption_enabled,
            'is_otp_enabled' => $this->is_otp_enabled,
            'storage_limit' => $this->storage_limit,
            'dark_mode' => $this->dark_mode,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'last_login_ip', $this->last_login_ip])
            ->andFilterWhere(['like', 'bio', $this->bio])
            ->andFilterWhere(['like', 'role', $this->role])
            ->andFilterWhere(['like', 'encryption_key', $this->encryption_key])
            ->andFilterWhere(['like', 'otp_secret', $this->otp_secret])
            ->andFilterWhere(['like', 'recovery_codes', $this->recovery_codes])
            ->andFilterWhere(['like', 'vault_secret', $this->vault_secret])
            ->andFilterWhere(['like', 'vault_salt', $this->vault_salt]);

        return $dataProvider;
    }
}
