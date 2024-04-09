<?php
/* @var $this yii\web\View */

use app\assets\FontAwesomeAsset;
use yii\bootstrap5\Html;

FontAwesomeAsset::register($this);
$this->title = '系统信息';
?>

<div class="system-info">

    <h1><?= Html::encode($this->title) ?></h1>

    <div>
        <div>
            <h2>
                <i class="fa-solid fa-server"></i>
                <!--hostname-->
            </h2>
            <p>
                OS:
                <strong><!--Server Operate System--></strong>
            </p>
            <p>
                CPU:
                <strong><!--CPU--></strong>
            </p>
            <p>
                RAM:
                <strong><!--RAM--></strong>
            </p>
            <p>
                Server Time:
                <strong><!--Server Time--></strong>
            </p>
            <p>
                Server Up Time:
                <strong><!--Server Up Time--></strong>
            </p>
        </div>
        <hr>
        <div>
            <div>
                <h2>
                    <i class="fa-solid fa-bars-progress"></i>
                    Load
                </h2>
                <!-- Load Graph -->
                <p>
                    <!-- Load value -->
                </p>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-microchip"></i>
                    CPU
                </h2>
                <!-- CPU Graph -->
                <p>
                    <!-- CPU value -->
                </p>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-memory"></i>
                    RAM
                </h2>
                <!-- RAM Graph -->
                <p>
                    <!-- RAM value -->
                </p>
                <p>
                    <!-- SWAP value -->
                </p>
            </div>
        </div>
        <hr>
        <div>
            <div>
                <h2>
                    <i class="fa-solid fa-hard-drive"></i>
                    Disk
                </h2>
            </div>
            <div>
                <div>
                    <!-- disk chart -->
                </div>
                <div>
                    <h3>Data</h3>
                    Mount:
                    <span><!-- Mount point | Drive letter--></span>
                    <br>
                    File System:
                    <span><!-- File System--></span>
                    <br>
                    Size:
                    <span><!-- Size--></span>
                    <br>
                    Free:
                    <span><!-- Free--></span>
                    <br>
                    Used:
                    <span><!-- Used--></span>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <h2>
                <i class="fa-solid fa-ethernet"></i>
                Network
            </h2>
            <p>
                Hostname:
                <!-- Hostname -->
            </p>
            <p>
                DNS:
                <!-- DNS -->
            </p>
            <p>
                Gateway:
                <!-- Gateway -->
            </p>
            <div>
                <!-- 数量基于实际情况 -->
                <div>
                    <div>
                        <h3><!--Interface Name--></h3>
                        Status:
                        <span><!--Status--></span>
                        <br>
                        Speed:
                        <span><!--Speed--></span>
                        <br>
                        IPv4:
                        <span><!--IPv4--></span>
                        <br>
                        IPv6:
                        <span><!--IPv6--></span>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <h2>
                <i class="fa-solid fa-users"></i>
                Active Users
            </h2>
            <div>
                All:
                <span><!-- All Users--></span>
                <br>
                Active (within 24h):
                <span><!-- Active Users--></span>
            </div>
        </div>
        <hr>
        <div>
            <div>
                <h2>
                    <i class="fa-solid fa-share-nodes"></i>
                    Share
                </h2>
                <div>
                    Link:
                    <span><!-- The Number of Share Link--></span>
                </div>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-paper-plane"></i>
                    Collection
                </h2>
                <div>
                    Collection:
                    <span><!-- The Number of Collection Link--></span>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <div>
                <h2>
                    <i class="fa-brands fa-php"></i>
                    PHP
                </h2>
                <div>
                    Version:
                    <span><!-- PHP Version--></span>
                    <br>
                    Memory Limit:
                    <span><!-- Memory Limit--></span>
                    <br>
                    Max Execution Time:
                    <span><!-- Max Execution Time--></span>
                    <br>
                    Upload Max Filesize:
                    <span><!-- Upload Max Filesize--></span>
                    <br>
                    Post Max Size:
                    <span><!-- Post Max Size--></span>
                    <br>
                    Extension:
                    <span><!-- Extension--></span>
                </div>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-database"></i>
                    Database
                </h2>
                <div>
                    Type:
                    <span><!-- Database Type--></span>
                    <br>
                    Version:
                    <span><!-- Database Version--></span>
                    <br>
                    Size:
                    <span><!-- Database Size--></span>
                </div>
            </div>
        </div>
    </div>
</div>
