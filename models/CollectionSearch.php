<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CollectionSearch represents the model behind the search form of `app\models\CollectionTasks`.
 */
class CollectionSearch extends CollectionTasks
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id','status'], 'integer'],
            [['folder_path', 'created_at', 'secret'], 'safe'],
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
        // if user is admin
        if (Yii::$app->user->can('admin')) {
            $query = CollectionTasks::find();
        } else {
            $query = CollectionTasks::find()->where(['user_id' => Yii::$app->user->id]);
        }

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
        if (Yii::$app->user->can('admin')) {
            $query->andFilterWhere([
                'id' => $this->id,
                'user_id' => $this->user_id,
                'created_at' => $this->created_at,
                'status'=>$this->status,
            ]);
        }else{
            $query->andFilterWhere([
                'id' => $this->id,
                'created_at' => $this->created_at,
                'status'=>$this->status,
            ]);
        }

        $query->andFilterWhere(['like', 'folder_path', $this->folder_path])
            ->andFilterWhere(['like', 'secret', $this->secret]);

        return $dataProvider;
    }
}
