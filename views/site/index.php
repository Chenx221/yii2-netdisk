<?php

/** @var yii\web\View $this */

use app\assets\TSParticlesAsset;
use yii\helpers\Url;

$this->title = '首页';
TSParticlesAsset::register($this);
?>
    <div id="tsparticles" style="position: absolute;z-index: -1"></div>
    <div class="site-index">
        <div class="jumbotron text-center bg-transparent mt-5 mb-5">
            <?php if (Yii::$app->user->isGuest): ?>
                <h1 class="display-4">这里是<?= Yii::$app->name ?>的首页</h1>
                <p>你需要登录或注册账户才能访问更多内容</p>
                <p><a class="btn btn-lg btn-primary" href="<?= Url::to(['/user/register']) ?>">注册账户</a></p>
                <p><a class="btn btn-lg btn-success" href="<?= Url::to(['/user/login']) ?>">登录账户</a></p>
            <?php elseif (Yii::$app->user->can('user')): ?>
                <p>系统公告</p>
                <div class="body-content">

                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <h2>Heading</h2>

                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                incididunt ut
                                labore
                                et
                                dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
                                nisi
                                ut
                                aliquip
                                ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                cillum
                                dolore eu
                                fugiat nulla pariatur.</p>

                            <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/doc/">Yii
                                    Documentation
                                    &raquo;</a></p>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <h2>Heading</h2>

                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                incididunt ut
                                labore
                                et
                                dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
                                nisi
                                ut
                                aliquip
                                ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                cillum
                                dolore eu
                                fugiat nulla pariatur.</p>

                            <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/forum/">Yii Forum
                                    &raquo;</a>
                            </p>
                        </div>
                        <div class="col-lg-4">
                            <h2>Heading</h2>

                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                incididunt ut
                                labore
                                et
                                dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
                                nisi
                                ut
                                aliquip
                                ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                cillum
                                dolore eu
                                fugiat nulla pariatur.</p>

                            <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/extensions/">Yii
                                    Extensions
                                    &raquo;</a></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p>你已经登录，欢迎回来</p>
            <?php endif; ?>
        </div>
    </div>
<?php
$js = <<<JS
async function loadParticles(options) {
  await tsParticles.load({ id: "tsparticles", options });
}

const configs = {
    fpsLimit: 144,
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
        speed: 3,
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
          distance: 280,
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

loadParticles(configs);

JS;

$this->registerJs($js);
?>