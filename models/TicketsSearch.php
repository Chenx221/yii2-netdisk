<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

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
        // if can admin
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
                'id' => $this->id,
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

    /**
     * give me the count of pending tickets
     * @return bool|int|string|null
     */
    public static function getPendingTicketsCount(): bool|int|string|null
    {
        //Tickets::STATUS_OPEN or Tickets::STATUS_USER_REPLY
        return Tickets::find()->where(['status' => [Tickets::STATUS_OPEN, Tickets::STATUS_USER_REPLY]])->count();
    }
}
