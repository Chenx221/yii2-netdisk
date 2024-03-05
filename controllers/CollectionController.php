<?php

namespace app\controllers;

use app\models\CollectionTasks;
use app\models\CollectionSearch;
use app\models\CollectionUploaded;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Response;
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
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'view', 'create', 'delete'],
                            'roles' => ['user'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['access', 'upload'],
                            'roles' => ['?', '@'], // everyone can access public share
                        ]
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'view' => ['GET'],
                        'create' => ['POST'],
                        'delete' => ['POST'],
                        'access' => ['GET'],
                        'upload' => ['POST'],
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
    public function actionIndex(): string
    {
        $searchModel = new CollectionSearch();
        if ($this->request instanceof Request) {
            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query->andWhere(['!=', 'status', 0]);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new RuntimeException('Invalid request type');
        }
    }

    /**
     * Displays a single CollectionTasks model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CollectionTasks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate(): Response|string
    {
        $model = new CollectionTasks();
        $model->scenario = 'create'; // 设置场景

        if ($this->request instanceof Request && $this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->user_id = Yii::$app->user->id; // 手动设置user_id字段的值
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }
        //不允许get，所以无视下面这个return
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CollectionTasks model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        // 获取模型
        $model = $this->findModel($id);

        // 设置状态为禁用
        $model->status = 0;

        // 保存模型
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Task delete successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete task.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the CollectionTasks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CollectionTasks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id, bool $is_public = false): CollectionTasks
    {
        // Not Allow to access other user's collection manage page
        // public collection can be accessed by anyone
        if (($model = CollectionTasks::findOne(['id' => $id])) !== null && ($is_public || $model->user_id === Yii::$app->user->id)) {
            return $model;
        }

        throw new NotFoundHttpException('没有权限访问这个页面或请求的页面不存在');
    }

    /**
     * 外部收集文件访问接口，接受参数id:收集文件任务id,secret:访问密钥,CollectionTasks[secret]:访问密钥(另一种形式)
     *
     * @param $id
     * @param $secret
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAccess($id = null, $secret = null): string
    {
        $receive_secret = Yii::$app->request->get('CollectionTasks')['secret'] ?? null;
        if (!is_null($receive_secret)) {
            $secret = $receive_secret;
        }
        $model = $this->findModel($id, true);
        if ($model === null | $model->status === 0) {
            throw new NotFoundHttpException('请求的文件收集任务已失效或不存在');
        } elseif (!is_dir(Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->user_id . '/' . $model->folder_path)) {
            throw new NotFoundHttpException('收集任务的目标路径不存在');
        } elseif ($secret === null) {
            return $this->render('_gateway', [
                'model' => new CollectionTasks(),
            ]);
        } elseif ($model->secret !== $secret) {
            Yii::$app->session->setFlash('error', '访问凭证不正确');
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
    public function actionUpload(): Response
    {
        $request = Yii::$app->request;

        // 获取POST请求中的参数
        $taskId = $request->post('CollectionTasks')['id'];
        $subfolderName = $request->post('CollectionUploaded')['subfolder_name'];

        // 获取发送POST请求的用户的IP地址
        $uploaderIp = $request->userIP;

        $task = $this->findModel($taskId, true); //CollectionTasks::findOne($taskId);
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
