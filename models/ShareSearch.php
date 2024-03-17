<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShareSearch represents the model behind the search form of `app\models\Share`.
 */
class ShareSearch extends Share
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['share_id', 'sharer_id'], 'integer'],
            [['file_relative_path', 'access_code', 'creation_date'], 'safe'],
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
        $query = Share::find()->where(['sharer_id' => Yii::$app->user->id]);

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
            'share_id' => $this->share_id,
//            'sharer_id' => $this->sharer_id,
            'creation_date' => $this->creation_date,
        ]);

        $query->andFilterWhere(['like', 'file_relative_path', $this->file_relative_path])
            ->andFilterWhere(['like', 'access_code', $this->access_code]);

        return $dataProvider;
    }
}
