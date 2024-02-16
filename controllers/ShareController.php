<?php

namespace app\controllers;

use app\models\Share;
use app\models\ShareSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ShareController implements the CRUD actions for Share model.
 */
class ShareController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Share models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ShareSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Share model.
     * @param int $share_id Share ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($share_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($share_id),
        ]);
    }

    /**
     * Creates a new Share model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Share();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->sharer_id = Yii::$app->user->id;  // 自动设置 sharer_id 为当前用户的 ID
                if ($model->save()) {
                    return $this->redirect(['view', 'share_id' => $model->share_id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Share model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $share_id Share ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($share_id)
    {
        $model = $this->findModel($share_id);
        $model->scenario = Share::SCENARIO_UPDATE;  // 设置模型为 'update' 场景

        if ($this->request->isPost) {
            $postData = $this->request->post();
            if (isset($postData['Share']['access_code'])) {  // 只加载 'access_code' 字段
                $model->access_code = $postData['Share']['access_code'];
                if ($model->save()) {
                    return $this->redirect(['view', 'share_id' => $model->share_id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Share model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $share_id Share ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($share_id)
    {
        $this->findModel($share_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Share model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $share_id Share ID
     * @return Share the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($share_id)
    {
        if (($model = Share::findOne(['share_id' => $share_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
