<?php

namespace app\controllers;

use app\models\CollectionTasks;
use app\models\CollectionSearch;
use app\models\CollectionUploaded;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * CollectionController implements the CRUD actions for CollectionTasks model.
 */
class CollectionController extends Controller
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
                        'upload' => ['POST']
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all CollectionTasks models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CollectionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CollectionTasks model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CollectionTasks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new CollectionTasks();
        $model->scenario = 'create'; // 设置场景

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->user_id = Yii::$app->user->id; // 手动设置user_id字段的值
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
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
     * Deletes an existing CollectionTasks model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CollectionTasks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CollectionTasks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CollectionTasks::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 外部收集文件访问接口，接受参数id:收集文件任务id,secret:访问密钥,CollectionTasks[secret]:访问密钥(另一种形式)
     *
     * @param $id
     * @param $secret
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAccess($id = null, $secret = null)
    {
        $receive_secret = Yii::$app->request->get('CollectionTasks')['secret'] ?? null;
        if (!is_null($receive_secret)) {
            $secret = $receive_secret;
        }
        $model = CollectionTasks::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('请求的文件收集任务不存在');
        } elseif (!is_dir(Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->user_id . '/' . $model->folder_path)) {
            throw new NotFoundHttpException('收集任务的目标路径不存在');
        } elseif ($secret === null) {
            return $this->render('_gateway', [
                'model' => new CollectionTasks(),
            ]);
        } elseif ($model->secret !== $secret) {
            Yii::$app->session->setFlash('error', '拒绝访问，凭证不正确');
            return $this->render('_gateway', [
                'model' => new CollectionTasks(),
            ]);
        } else {
            $model2 = new CollectionUploaded();
            do {
                $model2->subfolder_name = Uuid::uuid4()->toString();
                $path = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->user_id . '/' . $model->folder_path . '/' . $model2->subfolder_name;
            } while (file_exists($path));
            return $this->render('access', [
                'model' => $model,
                'model2' => $model2,
            ]);
        }
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpload()
    {
        $request = Yii::$app->request;

        // 获取POST请求中的参数
        $taskId = $request->post('CollectionTasks')['id'];
        $subfolderName = $request->post('CollectionUploaded')['subfolder_name'];

        // 获取发送POST请求的用户的IP地址
        $uploaderIp = $request->userIP;

        $task = CollectionTasks::findOne($taskId);
        $userId = $task->user_id;
        $folderPath = $task->folder_path;

        // 创建一个新的CollectionUploaded模型实例，并设置其属性值
        $model = new CollectionUploaded();
        $model->task_id = $taskId;
        $model->uploader_ip = $uploaderIp;
        $model->uploaded_at = date('Y-m-d H:i:s'); // 设置上传时间为当前时间
        $model->subfolder_name = $subfolderName;
        if ($model->validate()) {
            // 进行文件上传
            $targetDirectory = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $userId . '/' . $folderPath . '/' . $subfolderName;
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }
            $uploadedFiles = UploadedFile::getInstancesByName('files');
            foreach ($uploadedFiles as $file) {
                $filePath = $targetDirectory . '/' . $file->name;
                if (!$file->saveAs($filePath)) {
                    throw new NotFoundHttpException('文件上传失败');
                }
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '上传完成');
                return $this->redirect(['access', 'id' => $taskId, 'secret' => $task->secret]);
            } else {
                // 如果保存失败，可以抛出一个异常，或者渲染一个错误页面
                throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
            }
        }
        throw new NotFoundHttpException('上传失败,验证错误');
    }
}
