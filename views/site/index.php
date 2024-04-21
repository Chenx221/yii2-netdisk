<?php

/** @var yii\web\View $this */

/** @var array $latestAnnouncements */

use app\assets\TSParticlesAsset;
use yii\helpers\Url;

$this->title = '首页';
TSParticlesAsset::register($this);
//$latestAnnouncements = []; emulating empty data
?>
    <div id="tsparticles" style="position: absolute;z-index: -1"></div>
    <div class="site-index">
        <div class="jumbotron text-center bg-transparent mt-5 mb-5">
            <?php if (Yii::$app->user->isGuest): ?>
                <h1 class="display-4">这里是<?= Yii::$app->name ?>的首页</h1>
                <p>你需要登录或注册账户才能访问更多内容</p>
                <p><a class="btn btn-lg btn-primary" href="<?= Url::to(['/user/register']) ?>">注册账户</a></p>
                <p><a class="btn btn-lg btn-success" href="<?= Url::to(['/user/login']) ?>">登录账户</a></p>
            <?php elseif (Yii::$app->user->can('user') || Yii::$app->user->can('admin')): ?>
                <h1>系统公告</h1>
                <br>
                <div class="body-content">
                    <div class="row">
                        <?php if (empty($latestAnnouncements)): ?>
                            <div class="rounded shadow-sm" style="height:fit-content;backdrop-filter: blur(3px);">
                                <h4>暂无公告</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($latestAnnouncements as $announcement): ?>
                                <div class="col-lg-4 mb-3 rounded shadow-sm"
                                     style="height:fit-content;backdrop-filter: blur(3px);">
                                    <h4><?= mb_strimwidth($announcement->title, 0, 30, "...", "UTF-8") ?></h4>
                                    <p><?= mb_strimwidth($announcement->content, 0, 240, "...", "UTF-8") ?></p>
                                    <p>
                                        <a class="btn btn-outline-secondary" href=""
                                           data-aid="<?= $announcement->id ?>">查看详情 &raquo;</a>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="announcementModalLabel">公告详情</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <!-- 这里将显示公告的内容 -->
                                <h4 id="announcementTitle"></h4>
                                <p id="announcementTime"></p>
                                <p id="announcementContent"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">已读</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
$js = <<<JS
$(document).ready(function() {
  $('.btn-outline-secondary').click(function(e) {
    e.preventDefault();
    $('#announcementModal').modal('show');
    // 显示加载指示器
    $('.spinner-border').show();

    // 获取公告的 ID
    var announcementId = $(this).data('aid');

    // 发送 AJAX 请求
    $.ajax({
      url: 'index.php?r=site/get-announcement&id=' + announcementId,
      type: 'GET',
      success: function(data) {
        // 隐藏加载指示器
        $('.spinner-border').hide();

        // 使用返回的数据填充模态框
        $('#announcementTitle').text(data.title);
        $('#announcementTime').text("最近修改时间: "+data.updated_at);
        $('#announcementContent').text(data.content);
      },
      error: function() {
        // 隐藏加载指示器
        $('.spinner-border').hide();

        // 显示错误消息
        alert('无法加载公告详情');
      }
    });
  });
});
async function loadParticles(options) {
    
  await tsParticles.load({ id: "tsparticles", options });
}

const configs = {
    fpsLimit: 60,
    particles: {
      number: {
        value: 100,
        density: {
          enable: true,
          area: 800
        }
      },
      color: {
        value: ["#2EB67D", "#ECB22E", "#E01E5B", "#36C5F0"]
      },
      shape: {
        type: "circle"
      },
      opacity: {
        value: 0.4
      },
      size: {
        value: { min: 4, max: 8 }
      },
      links: {
        enable: true,
        distance: 150,
        color: "#808080",
        opacity: 0.4,
        width: 1
      },
      move: {
        enable: true,
        speed: 1,
        outModes: {
          default: "out"
        }
      }
    },
    interactivity: {
      events: {
        onHover: {
          enable: true,
          mode: "grab"
        },
        onClick: {
          enable: true,
          mode: "push"
        }
      },
      modes: {
        grab: {
          distance: 230,
          links: {
            opacity: 1,
            color: "#808080"
          }
        },
        push: {
          quantity: 4
        }
      }
    }
  };

loadParticles(configs).then(r => console.log("Particles loaded"));

JS;

$this->registerJs($js);
?>