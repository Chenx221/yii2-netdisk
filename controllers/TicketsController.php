<?php

namespace app\controllers;

use app\models\TicketReplies;
use app\models\Tickets;
use app\models\TicketsSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TicketsController implements the CRUD actions for Tickets model.
 */
class TicketsController extends Controller
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
                            'actions' => ['index', 'view', 'create', 'update', 'delete', 'reply'],
                            'roles' => ['user'], // only user can do these
                        ]
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'view' => ['GET'],
                        'create' => ['GET', 'POST'],
                        'update' => ['GET', 'POST'],
                        'delete' => ['POST'],
                        'reply' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Tickets models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new TicketsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tickets model.
     * @param int $id 工单id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        //check if this ticket belongs to current user
        $ticket = Tickets::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        if ($ticket === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        //fetch all replies for this ticket
        $ticketReplies = $this->findTicketReplies($id);
        //json
        $json = json_encode($ticketReplies);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'ticketReplies' => $json,
        ]);
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
     * Creates a new Tickets model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate(): Response|string
    {
        $model = new Tickets();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // add properties that are not in the form
                $model->user_id = Yii::$app->user->id;
                $model->status = Tickets::STATUS_OPEN;
                $model->ip = $this->request->userIP;
                $model->created_at = date('Y-m-d H:i:s');
                $model->updated_at = date('Y-m-d H:i:s');

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', '工单创建成功');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * NoNoNo, you can't delete a ticket. Just close it.
     * @param int $id 工单id
     * @param string $from
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id,string $from = 'unset'): Response
    {
        $this->findModel($id)->updateAttributes(['status' => Tickets::STATUS_CLOSED]);
        Yii::$app->session->setFlash('info', '工单已关闭，如果有任何问题，你可以重新建立新工单或在旧工单上回复以重新打开工单。');
        if ($from !== 'unset'){
            return $this->redirect(['index']);
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Tickets model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id 工单id
     * @return Tickets the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Tickets
    {
        if (($model = Tickets::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Ticket reply action
     * For user
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReply(): Response
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $ticketId = $request->post('ticketId');

            // check this ticket exists and belongs to current user
            $ticket = Tickets::findOne(['id' => $ticketId, 'user_id' => Yii::$app->user->id]);
            if ($ticket === null) {
                return $this->asJson(['status' => 'error', 'message' => 'Invalid ticket']);
            }

            // 创建一个新的TicketReplies对象
            $reply = new TicketReplies();
            $reply->ticket_id = $ticketId;
            $reply->user_id = Yii::$app->user->id; // 设置为当前用户的ID
            $reply->message = $request->post('content');
            $reply->ip = $request->userIP;
            $reply->is_admin = 0; // 设置为用户回复
            $reply->created_at = date('Y-m-d H:i:s');

            if ($reply->save()) {
                // 如果保存成功，返回一个成功的响应
                $this->findModel($ticketId)->updateAttributes(['status' => Tickets::STATUS_USER_REPLY]);
                return $this->asJson(['status' => 'success']);
            } else {
                // 如果保存失败，返回一个包含错误信息的响应
                return $this->asJson(['status' => 'error', 'errors' => $reply->errors]);
            }
        }

        // 如果不是POST请求，返回一个错误响应
        return $this->asJson(['status' => 'error', 'message' => 'Invalid request']);
    }
}
