<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Tickets;

/**
 * TicketsSearch represents the model behind the search form of `app\models\Tickets`.
 */
class TicketsSearch extends Tickets
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id', 'status'], 'integer'],
            [['title', 'description', 'created_at', 'updated_at','ip'], 'safe'],
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
        $query = Tickets::find();

        // add conditions that should always apply here

        $dataProvider = null;
        if(Yii::$app->user->can('admin')) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query->where(['user_id' => Yii::$app->user->id]),
            ]);
        }
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        // if can user
        if(Yii::$app->user->can('admin')) {
            $query->andFilterWhere([
                'id' => $this->id,
                'user_id' => $this->user_id,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]);
        } else{
            $query->andFilterWhere([
                'user_id' => $this->user_id,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]);
        }
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'ip', $this->ip]);

        return $dataProvider;
    }
}
