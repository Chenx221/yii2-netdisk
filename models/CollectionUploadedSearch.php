<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CollectionUploadedSearch represents the model behind the search form of `app\models\CollectionUploaded`.
 */
class CollectionUploadedSearch extends CollectionUploaded
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'task_id'], 'integer'],
            [['uploader_ip', 'uploaded_at', 'subfolder_name'], 'safe'],
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
        $query = CollectionUploaded::find();

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
            'task_id' => $this->task_id,
            'uploaded_at' => $this->uploaded_at,
        ]);

        $query->andFilterWhere(['like', 'uploader_ip', $this->uploader_ip])
            ->andFilterWhere(['like', 'subfolder_name', $this->subfolder_name]);

        return $dataProvider;
    }
}
