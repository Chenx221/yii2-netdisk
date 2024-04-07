<?php

namespace app\controllers;

use app\models\DownloadLogs;
use app\models\Share;
use app\models\ShareSearch;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Response;
use ZipArchive;

/**
 * ShareController implements the CRUD actions for Share model.
 */
class ShareController extends Controller
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
                            'actions' => ['index', 'view', 'create', 'update', 'delete'],
                            'roles' => ['user'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['access', 'download'],
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
                        'update' => ['GET', 'POST'],
                        'delete' => ['POST'],
                        'access' => ['GET', 'POST'],
                        'download' => ['GET'],
                    ],
                ],
            ]
        );
    }

    public function init(): void
    {
        parent::init();

        if (Yii::$app->user->can('admin')) {
            $this->layout = 'admin_main';
        } elseif (Yii::$app->user->isGuest) {
            $this->layout = 'guest_main';
        } else {
            $this->layout = 'main';
        }
    }

    /**
     * Lists all Share models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new ShareSearch();
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
     * Displays a single Share model.
     * @param int $share_id Share ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $share_id): string
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
    public function actionCreate(): Response|string
    {
        $model = new Share();

        if ($this->request instanceof Request && $this->request->isPost) {
            if ($model->load($this->request->post())) {
                //对access_code进行检测
                if (empty($model->access_code) || !preg_match('/^[a-zA-Z0-9]{4}$/', $model->access_code)) {
                    Yii::$app->session->setFlash('error', '访问密码必须是4位字母和数字的组合');
                    return $this->render('create', ['model' => $model]);
                }

                //对file_relative_path进行检测
                $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $model->file_relative_path;
                if (!file_exists($absolutePath)) {
                    Yii::$app->session->setFlash('error', '文件或文件夹不存在');
                    return $this->render('create', ['model' => $model]);
                }

                $model->access_code = strtolower($model->access_code);
                $model->sharer_id = Yii::$app->user->id;  // 自动设置 sharer_id 为当前用户的 ID
                if ($model->save()) {
                    return $this->redirect(['view', 'share_id' => $model->share_id]);
                }
            }
        }
        // 看看就好，不给用get的
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
    public function actionUpdate(int $share_id): Response|string
    {
        $model = $this->findModel($share_id);
        $model->scenario = Share::SCENARIO_UPDATE;  // 设置模型为 'update' 场景

        if ($this->request instanceof Request && $this->request->isPost) {
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
     * 注: 不是真的删除
     * @param int $share_id Share ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $share_id): Response
    {
        // 获取模型
        $model = $this->findModel($share_id);

        // 设置状态为禁用
        $model->status = 0;

        // 保存模型
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Share delete successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete share.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Share model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $share_id Share ID
     * @return Share the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $share_id, bool $is_public = false): Share
    {
        // Not Allow to access other user's share manage page
        // public share can be accessed by anyone
        if (($model = Share::findOne(['share_id' => $share_id])) !== null && ($is_public || $model->sharer_id == Yii::$app->user->id)) {
            return $model;
        }

        throw new NotFoundHttpException('没有权限访问这个页面或请求的页面不存在');
    }

    /**
     * 分享的公开访问点
     * @param $share_id
     * @param $access_code
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAccess($share_id, $access_code = null): string
    {
        $model = $this->findModel($share_id, true);
        //检查文件/文件夹是否存在
        $abp = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->sharer_id . '/' . $model->file_relative_path;
        if (!file_exists($abp) || $model->status == 0) {
            throw new NotFoundHttpException('分享失效，文件或文件夹不存在');
        }
        if ($this->request instanceof Request && $this->request->isPost) {
            $access_code = $this->request->post('Share')['access_code'];
            if ($access_code === $model->access_code) {
                // 访问密码正确，显示文件信息和下载按钮
                $access_granted = Yii::$app->session->get('access_granted', []);
                $access_granted[$share_id] = true;
                Yii::$app->session->set('access_granted', $access_granted);  // 将访问权限信息存储到 session 中
                $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->sharer_id . '/' . $model->file_relative_path;
                $isDirectory = is_dir($absolutePath);
                $sharerUsername = $model->getSharerUsername();
                return $this->render('_file_info', [
                    'model' => $model,
                    'isDirectory' => $isDirectory,
                    'sharerUsername' => $sharerUsername,
                ]);
            } else {
                Yii::$app->session->setFlash('error', '访问密码错误');
            }
        }
        $model1 = new Share();
        $model1->access_code = $access_code;

        return $this->render('access', [
            'model' => $model1,
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDownload($share_id): Response|\yii\console\Response
    {
        $access_granted = Yii::$app->session->get('access_granted', []);
        if (!isset($access_granted[$share_id]) || !$access_granted[$share_id]) {
            throw new ForbiddenHttpException('你没有权限下载这个文件');
        }

        $model = $this->findModel($share_id, true);
        DownloadLogs::addLog(Yii::$app->user->id, $model->share_id, Yii::$app->request->userIP, Yii::$app->request->userAgent); // logging for access(DL)
        // add download count
        $model->setDlCountPlus1();
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->sharer_id . '/' . $model->file_relative_path;
        if (is_file($absolutePath)) {
            return Yii::$app->response->sendFile($absolutePath);
        } else if (is_dir($absolutePath)) {
            // 如果是文件夹，压缩后发送
            $zipPath = $absolutePath . '.zip';
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath));
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($absolutePath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            return Yii::$app->response->sendFile($zipPath);
        } else {
            throw new NotFoundHttpException('异常，文件不存在');
        }
    }

    // TODO
    // Preview file
}
