<?php

use app\assets\FontAwesomeAsset;
use app\assets\QuillAsset;
use app\models\Tickets;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Tickets $model */
/** @var string $ticketReplies */

$this->title = '工单: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => '工单支持', 'url' => ['index']];
$this->params['breadcrumbs'][] = '工单ID ' . $model->id;
YiiAsset::register($this);
QuillAsset::register($this);
FontAwesomeAsset::register($this);
$this->registerCssFile('@web/css/tickets.css');
?>
    <div class="tickets-view">

        <h1><?= Html::encode($this->title) ?></h1>
        <br>


        <div class="row">
            <div class="col-md-3">
                <!-- DetailView -->
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'status',
                            'label' => '状态',
                            'format' => 'raw', // 使用 raw 格式，这样 Yii2 不会对 value 的返回值进行 HTML 编码
                            'value' => function (Tickets $model) {
                                return match ($model->status) {
                                    Tickets::STATUS_OPEN => '<span class="badge rounded-pill bg-primary">工单已开启</span>',
                                    Tickets::STATUS_ADMIN_REPLY => '<span class="badge rounded-pill bg-info">管理员已回复</span>',
                                    Tickets::STATUS_USER_REPLY => '<span class="badge rounded-pill bg-secondary">用户已回复</span>',
                                    Tickets::STATUS_CLOSED => '<span class="badge rounded-pill bg-success">工单已关闭</span>',
                                    default => '<span class="badge rounded-pill bg-danger">未知状态</span>',
                                };
                            }
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => '创建时间',
                            'format' => 'raw', // 使用 raw 格式，这样 Yii2 不会对 value 的返回值进行 HTML 编码
                            'value' => function (Tickets $model) {
                                $dateTime = new DateTime($model->created_at, new DateTimeZone('GMT+8'));
                                return $model->created_at . '<br>(' . Yii::$app->formatter->asRelativeTime($dateTime) . ')';
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'label' => '最近更新时间',
                            'format' => 'raw', // 使用 raw 格式，这样 Yii2 不会对 value 的返回值进行 HTML 编码
                            'value' => function (Tickets $model) {
                                $dateTime = new DateTime($model->updated_at, new DateTimeZone('GMT+8'));
                                return $model->updated_at . '<br>(' . Yii::$app->formatter->asRelativeTime($dateTime) . ')';
                            }
                        ]
                    ],
                ]) ?>
                <p>
                    <?= ($model->status===Tickets::STATUS_CLOSED)?'':Html::a('关闭工单', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => '您确定要关闭这个工单吗？问题已经解决了吗？',
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>
            </div>
            <div class="col-md-9">
                <!-- User message input and ticket content -->
                <div class="form-control">
                    <div id="editor" class="form-control">

                    </div>
                    <?= Html::button('回复', ['class' => 'btn btn-primary', 'id' => 'send']) ?>
                </div>
                <div id="ticket-content">
                    <br>
                </div>
            </div>
        </div>

    </div>
<?php
$core_js = <<<JS
//写的很乱
    var theme = document.documentElement.getAttribute('data-bs-theme')==='dark'?'bubble':'snow'
    const quill = new Quill('#editor', {
        theme: theme
    });
    $('#send').on('click', function() {
        var content = quill.getContents();
        // check content not empty
        if (quill.getLength()===1) {
            alert('内容不能为空');
            return false;
        }
        var ticketId = $model->id; // 你需要在这里设置正确的工单ID
    
        $.ajax({
            url: 'index.php?r=tickets%2Freply',
            type: 'POST',
            data: {
                ticketId: ticketId,
                content: JSON.stringify(content)
            },
            success: function(response) {
                // 处理服务器的响应
                console.log(response);
                // 如果服务器返回的状态是成功，刷新页面
                if (response.status === 'success') {
                    location.reload();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // 处理错误
                console.error(textStatus, errorThrown);
            }
        });
    });
    function quillGetHTML(inputDelta,skipParse=false) {
        var delta = skipParse?inputDelta:JSON.parse(inputDelta);
        var tempQuill=new Quill(document.createElement("div"));
        tempQuill.setContents(delta);
        return tempQuill.root.innerHTML;
    }
    function generateReply(user, message, date) {
        return '<div class="ticket-reply"><div class="ticket-reply-top"><div class="user"><i class="fas fa-user-circle"></i><span class="name">'+user+'</span></div><div class="info"> <span class="type">用户</span> <div class="date">'+date+'</div> </div> </div> <div class="ticket-message">'+message+'</div></div>';
    }
    function generateAdminReply(user, message, date) {
        return '<div class="ticket-reply admin"> <div class="ticket-reply-top"> <div class="user"> <i class="fas fa-user-circle"></i> <span class="name">'+user+'</span> </div> <div class="info"> <span class="type">管理员</span> <div class="date">'+date+'</div> </div> </div> <div class="ticket-message">'+message+'</div></div>';
    }
    var ticketContent = $model->description;
    var ticketContentElement = $('#ticket-content');
    ticketContentElement.append(generateReply('您', quillGetHTML(ticketContent,true), '$model->created_at'));
    var TicketReplies = $ticketReplies;
    for (let i = 0; i < TicketReplies.length; i++) {
        let reply = TicketReplies[i];
        let message = quillGetHTML(reply.message);
        let date = reply.created_at;
        if (reply.is_admin !== 1) {
            ticketContentElement.append(generateReply(reply.name, message, date));
        } else {
            ticketContentElement.append(generateAdminReply(reply.name, message, date));
        }
    }
    
JS;

$this->registerJs($core_js);
?>