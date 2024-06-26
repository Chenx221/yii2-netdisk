<?php

namespace app\controllers;

use app\models\Announcements;
use app\models\AnnouncementsSearch;
use app\models\CollectionSearch;
use app\models\CollectionTasks;
use app\models\CollectionUploadedSearch;
use app\models\DownloadLogs;
use app\models\LoginLogs;
use app\models\Share;
use app\models\ShareSearch;
use app\models\SiteConfig;
use app\models\TicketReplies;
use app\models\Tickets;
use app\models\TicketsSearch;
use app\models\User;
use app\models\UserSearch;
use app\utils\AdminSword;
use app\utils\FileSizeHelper;
use app\utils\SystemInfoHelper;
use OTPHP\TOTP;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

class AdminController extends Controller
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
                            'actions' => ['system', 'user', 'info', 'user-view', 'user-create', 'user-update',
                                'user-delete', 'user-totpoff', 'user-pwdreset', 'login-log', 'access-log', 'collection-up-log',
                                'share-manage', 'share-manage-view', 'share-manage-delete', 'collection-manage', 'collection-manage-view',
                                'collection-manage-delete', 'notice-manage', 'sysinfo', 'get-sysinfo', 'ticket-manage', 'ticket-view', 'ticket-delete',
                                'ticket-reply', 'announcements-manage', 'announcements-view', 'announcements-create', 'announcements-update', 'announcements-delete'],
                            'roles' => ['admin'], // only admin can do these
                        ]
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'system' => ['GET', 'POST'],
                        'user' => ['GET'],
                        'user-view' => ['GET', 'POST'],
                        'user-create' => ['GET', 'POST'],
                        'user-update' => ['GET', 'POST'],
                        'user-delete' => ['POST'],
                        'info' => ['GET', 'POST'],
                        'user-totpoff' => ['POST'],
                        'user-pwdreset' => ['POST'],
                        'login-log' => ['GET'],
                        'access-log' => ['GET'],
                        'collection-up-log' => ['GET'],
                        'share-manage' => ['GET'],
                        'share-manage-view' => ['GET'],
                        'share-manage-delete' => ['POST'],
                        'collection-manage' => ['GET'],
                        'collection-manage-view' => ['GET'],
                        'collection-manage-delete' => ['POST'],
                        'notice-manage' => ['GET'],
                        'sysinfo' => ['GET'],
                        'get-sysinfo' => ['GET'],
                        'ticket-manage' => ['GET'],
                        'ticket-view' => ['GET'],
                        'ticket-delete' => ['POST'],
                        'ticket-reply' => ['POST'],
                        'announcements-manage' => ['GET'],
                        'announcements-view' => ['GET'],
                        'announcements-create' => ['GET', 'POST'],
                        'announcements-update' => ['GET', 'POST'],
                        'announcements-delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function init(): void
    {
        parent::init();
        $this->layout = 'admin_main';
    }

    /**
     * @return string|Response
     * @throws HttpException
     */
    public function actionSystem(): Response|string
    {
        $siteConfig = new SiteConfig();
        if (!$siteConfig->loadFromEnv()) {
            throw new HttpException(500, 'Fatal error, Unable to load site configuration from . env file . ');
        }
        if (Yii::$app->request->isPost) {
            if ($siteConfig->load(Yii::$app->request->post()) && $siteConfig->validate()) {
                if ($siteConfig->saveToEnv()) {
                    Yii::$app->session->setFlash('success', '保存成功');
                    return $this->redirect(['system']);
                } else {
                    Yii::$app->session->setFlash('error', '保存失败');
                }
            }
        }
        return $this->render('system', [
            'siteConfig' => $siteConfig
        ]);
    }

    /**
     * Lists all User.
     *
     * @return string
     */
    public function actionUser(): string
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('user', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * 显示、处理用户管理中的数据变更
     * @param int $id ID
     * @throws NotFoundHttpException|Throwable if the model cannot be found
     */
    public function actionUserView(int $id): array|string
    {
        $model = $this->findModel($id);

        if (isset($_POST['hasEditable'])) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (isset($_POST['name'])) { //修改昵称
                $oldValue = $model->name;
                $model->name = $_POST['name'];
                if ($model->save(true, ['name'])) {
                    return ['output' => $model->name, 'message' => ''];
                } else {
                    return ['output' => $oldValue, 'message' => 'Incorrect Value!Please reenter . '];
                }
            } elseif (isset($_POST['status'])) { //修改用户状态
                if ($id == Yii::$app->user->id) {
                    return ['output' => 'ERROR', 'message' => 'You cannot change your own status'];
                }
                $oldValue = $model->status;
                $model->status = $_POST['status'];
                $status = $this->actionUserDelete($id, $model->status);
                if ($status == 1) {
                    return ['output' => $model->status == 0 ? '禁用' : '启用', 'message' => ''];
                } elseif ($status == 0) {
                    return ['output' => $oldValue == 0 ? '禁用' : '启用', 'message' => '删除失败'];
                } elseif ($status == 2) {
                    return ['output' => $oldValue == 0 ? '禁用' : '启用', 'message' => '无需操作'];
                } elseif ($status == 3) {
                    return ['output' => $oldValue == 0 ? '禁用' : '启用', 'message' => '操作不允许'];
                }
            } elseif (isset($_POST['bio'])) { //修改用户简介
                $oldValue = $model->bio;
                $model->bio = $_POST['bio'];
                if ($model->save(true, ['bio'])) {
                    return ['output' => $model->bio, 'message' => ''];
                } else {
                    return ['output' => $oldValue, 'message' => 'Incorrect Value!Please reenter . '];
                }
            } elseif (isset($_POST['storage_limit'])) { //修改用户存储限制
                $oldValue = $model->storage_limit;
                $input_limit = $_POST['storage_limit'];
                $limit = FileSizeHelper::getConvertedLimit($input_limit);
                switch ($limit) {
                    case -1:
                        $model->storage_limit = -1;
                        break;
                    case -2:
                        return ['output' => $oldValue, 'message' => '值不能为空'];
                    case -3:
                        return ['output' => $oldValue, 'message' => '格式错误'];
                    default:
                        $model->storage_limit = $limit;
                }
                if ($model->save(true, ['storage_limit'])) {
                    return ['output' => FileSizeHelper::formatMegaBytes($model->storage_limit), 'message' => ''];
                } else {
                    return ['output' => FileSizeHelper::formatMegaBytes($oldValue), 'message' => 'Incorrect Value!Please reenter . '];
                }

            } else {
                return ['output' => '', 'message' => ''];
            }
        }

        return $this->render('user_view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws Exception
     */
    public function actionUserCreate(): Response|string
    {
        $model = new User(['scenario' => 'addUser']);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {
                $raw_password = $model->password;
                $model->password = Yii::$app->security->generatePasswordHash($raw_password);
                $model->auth_key = Yii::$app->security->generateRandomString();
                $model->role = $this->request->post('User')['role'];
                $model->created_at = date('Y-m-d H:i:s');
                $model->name = $model->username; //用户默认昵称为用户名，后期可以修改
                if ($model->save(false)) { // save without validation
                    if ($model->role == 'user') {
                        $userFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id;
                        if (!is_dir($userFolder)) {
                            mkdir($userFolder);
                        }
                        $secretFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id . ' . secret';
                        if (!is_dir($secretFolder)) {
                            mkdir($secretFolder);
                        }
                    }
                    Yii::$app->session->setFlash('success', '用户添加成功');
                    return $this->redirect(['user-view', 'id' => $model->id]);
                } else {
                    $model->loadDefaultValues();
                    $model->password = $raw_password;
                    Yii::$app->session->setFlash('error', '用户添加失败');
                }
            }
        }
        $model->loadDefaultValues();
        return $this->render('user_create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUserUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['user_view', 'id' => $model->id]);
        }

        return $this->render('user_update', [
            'model' => $model,
        ]);
    }

    /**
     * 0: delete failed
     * 1: delete success
     * 2: nothing to do
     * 3: operation not allowed
     * @param int $id ID
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     */
    protected function actionUserDelete(int $id, int $operation): int
    {
        $user = $this->findModel($id);
        if ($user->status == $operation) {
            // nothing to do
            return 2;
        }
        if ($operation != 1 && $operation != 0) {
            return 3;
        }
        if ($user->deleteAccount($operation == 1)) {
            if ($operation == 0) {
                AdminSword::forceUserLogout($id);
            }
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionUserTotpoff(int $id)
    {
        try {
            $user = $this->findModel($id);
        } catch (NotFoundHttpException) {
            Yii::$app->session->setFlash('error', '用户不存在');
            return $this->redirect(['user']);
        }
        if ($user->is_otp_enabled) {
            $user->otp_secret = null;
            $user->is_otp_enabled = 0;
            $user->recovery_codes = null;
            $user->save(false);
            Yii::$app->session->setFlash('success', '二步验证已关闭');
        } else {
            Yii::$app->session->setFlash('error', '二步验证未启用,无需关闭');
        }
        return $this->redirect(['user-view', 'id' => $id]);
    }

    /**
     * @return Response
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionUserPwdreset(): Response
    {
        $id = Yii::$app->request->post('User')['id'];
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', '密码重置成功');
            } else {
                Yii::$app->session->setFlash('error', '密码重置失败');
            }
        }
        return $this->redirect(['user-view', 'id' => $id]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): User
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist . ');
    }

    /**
     * @param string|null $focus
     * @return Response|string
     */
    public function actionInfo(string $focus = null): Response|string
    {
        $model = Yii::$app->user->identity;
        $totp_secret = null;
        $totp_url = null;
        if (!$model->is_otp_enabled) {
            $totp = TOTP::generate();
            $totp_secret = $totp->getSecret();
            $totp->setLabel('NetDisk_' . $model->name);
            $totp_url = $totp->getProvisioningUri();
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->save(true, ['bio'])) {
                Yii::$app->session->setFlash('success', '个人简介已更新');
                return $this->render('info', [
                    'model' => $model,
                    'focus' => 'bio',
                    'totp_secret' => $totp_secret,
                    'totp_url' => $totp_url,
                    'is_otp_enabled' => $model->is_otp_enabled
                ]);
            }
        }
        return $this->render('info', [
            'model' => $model,
            'focus' => $focus,
            'totp_secret' => $totp_secret,
            'totp_url' => $totp_url,
            'is_otp_enabled' => $model->is_otp_enabled == 1
        ]);
    }

    /**
     * login log
     * @return string
     */
    public function actionLoginLog(): string
    {
        $loginLogs = new LoginLogs();
        $dataProvider = $loginLogs->search($this->request->queryParams);
        return $this->render('login_log', [
            'searchModel' => $loginLogs,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionAccessLog(): string
    {
        $downloadLogs = new DownloadLogs();
        $dataProvider = $downloadLogs->search($this->request->queryParams);
        return $this->render('access_log', [
            'searchModel' => $downloadLogs,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionCollectionUpLog(): string
    {
        $collectionUploadedSearch = new CollectionUploadedSearch();
        $dataProvider = $collectionUploadedSearch->search($this->request->queryParams);
        return $this->render('collection_up_log', [
            'searchModel' => $collectionUploadedSearch,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionShareManage(): string
    {
        $searchModel = new ShareSearch();
        if ($this->request instanceof Request) {
            $dataProvider = $searchModel->search($this->request->queryParams);
            return $this->render('share_manage', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new RuntimeException('Invalid request type');
        }
    }

    /**
     * @param int $share_id
     * @return Share
     * @throws NotFoundHttpException
     */
    protected function findShareModel(int $share_id): Share
    {
        if (($model = Share::findOne(['share_id' => $share_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $share_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShareManageView(int $share_id): string
    {
        return $this->render('share_manage_view', [
            'model' => $this->findShareModel($share_id),
        ]);
    }

    /**
     * @param int $share_id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionShareManageDelete(int $share_id): Response
    {
        $model = $this->findShareModel($share_id);
        $model->status = 0;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Share delete successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete share.');
        }
        return $this->redirect(['share-manage']);
    }

    /**
     * @return string
     */
    public function actionCollectionManage(): string
    {
        $searchModel = new CollectionSearch();
        if ($this->request instanceof Request) {
            $dataProvider = $searchModel->search($this->request->queryParams);
            return $this->render('collection_manage', [
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
    public function actionCollectionManageView(int $id): string
    {
        return $this->render('collection_manage_view', [
            'model' => $this->findCollectionModel($id),
        ]);
    }

    /**
     * @param int $id
     * @return CollectionTasks
     * @throws NotFoundHttpException
     */
    protected function findCollectionModel(int $id): CollectionTasks
    {
        if (($model = CollectionTasks::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCollectionManageDelete(int $id): Response
    {
        $model = $this->findCollectionModel($id);
        $model->status = 0;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Task delete successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete task.');
        }
        return $this->redirect(['collection-manage']);
    }

    /**
     * @return string
     */
    public function actionNoticeManage(): string
    {
        return $this->render('notice_manage');
    }

    /**
     * @return string
     */
    public function actionSysinfo(): string
    {
        $fullInfo = SystemInfoHelper::getSysInfoInit();
        return $this->render('sysinfo', [
            'systemInfo' => $fullInfo,
        ]);
    }

    /**
     * Get server status (mini)
     */
    public function actionGetSysinfo(): \yii\console\Response|Response
    {
        if (Yii::$app->request->isAjax) {
            $MiniInfo = SystemInfoHelper::getSysInfoFre();
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = $MiniInfo;

        } else {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = ['error' => 'Invalid request'];
        }
        return Yii::$app->response;
    }

    /**
     * @return string
     */
    public function actionTicketManage(): string
    {
        $searchModel = new TicketsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('ticket_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'pendingTickets' => TicketsSearch::getPendingTicketsCount()
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTicketView(int $id): string
    {
        //fetch all replies for this ticket
        $ticketReplies = $this->findTicketReplies($id);
        //json
        $json = json_encode($ticketReplies);
        return $this->render('ticket_view', [
            'model' => $this->findTicketModel($id),
            'ticketReplies' => $json,
        ]);
    }

    /**
     * @param int $id
     * @return Tickets
     * @throws NotFoundHttpException
     */
    protected function findTicketModel(int $id): Tickets
    {
        if (($model = Tickets::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $ticketId
     * @return array
     */
    protected function findTicketReplies(int $ticketId): array
    {
        $ticketReplies = TicketReplies::find()
            ->where(['ticket_id' => $ticketId])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($ticketReplies as $reply) {
            $result[] = $reply->toArray();
        }

        return $result;
    }

    /**
     * NoNoNo, you can't delete a ticket. Just close it.
     * @param int $id 工单id
     * @param string $from
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionTicketDelete(int $id, string $from = 'unset'): Response
    {
        $this->findTicketModel($id)->updateAttributes(['status' => Tickets::STATUS_CLOSED]);
        Yii::$app->session->setFlash('info', '工单已关闭');
        if ($from !== 'unset') {
            return $this->redirect(['ticket-manage']);
        }
        return $this->redirect(['ticket-view', 'id' => $id]);
    }

    /**
     * Ticket reply action
     * For user
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionTicketReply(): Response
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $ticketId = $request->post('ticketId');

            // check this ticket exists
            $ticket = Tickets::findOne(['id' => $ticketId]);
            if ($ticket === null) {
                return $this->asJson(['status' => 'error', 'message' => 'Invalid ticket']);
            }

            // 创建一个新的TicketReplies对象
            $reply = new TicketReplies();
            $reply->ticket_id = $ticketId;
            $reply->user_id = Yii::$app->user->id; // 设置为当前用户的ID
            $reply->message = $request->post('content');
            $reply->ip = $request->userIP;
            $reply->is_admin = 1; // 设置为用户回复
            $reply->created_at = date('Y-m-d H:i:s');

            if ($reply->save()) {
                // 如果保存成功，返回一个成功的响应
                $this->findTicketModel($ticketId)->updateAttributes(['status' => Tickets::STATUS_ADMIN_REPLY]);
                return $this->asJson(['status' => 'success']);
            } else {
                // 如果保存失败，返回一个包含错误信息的响应
                return $this->asJson(['status' => 'error', 'errors' => $reply->errors]);
            }
        }

        // 如果不是POST请求，返回一个错误响应
        return $this->asJson(['status' => 'error', 'message' => 'Invalid request']);
    }

    /**
     * Lists all Announcements models.
     *
     * @return string
     */
    public function actionAnnouncementsManage(): string
    {
        $searchModel = new AnnouncementsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('announcements_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Announcements model.
     * @param int $id 公告ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionAnnouncementsView(int $id): string
    {
        return $this->render('announcements_view', [
            'model' => $this->findAnnouncementsModel($id),
        ]);
    }

    /**
     * Creates a new Announcements model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionAnnouncementsCreate(): Response|string
    {
        $model = new Announcements();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // set published_at to current time
                $model->published_at = date('Y-m-d H:i:s');
                if($model->save()){
                    Yii::$app->session->setFlash('success', '公告发布成功');
                    return $this->redirect(['announcements-view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('announcements_create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Announcements model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id 公告ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionAnnouncementsUpdate(int $id): Response|string
    {
        $model = $this->findAnnouncementsModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '公告修改成功');
            return $this->redirect(['announcements-view', 'id' => $model->id]);
        }

        return $this->render('announcements_update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Announcements model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id 公告ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionAnnouncementsDelete(int $id): Response
    {
        $this->findAnnouncementsModel($id)->delete();
        Yii::$app->session->setFlash('success', '公告删除成功');
        return $this->redirect(['announcements-manage']);
    }

    /**
     * Finds the Announcements model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id 公告ID
     * @return Announcements the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findAnnouncementsModel(int $id): Announcements
    {
        if (($model = Announcements::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
