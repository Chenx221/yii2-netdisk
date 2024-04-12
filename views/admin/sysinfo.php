<?php
/* @var $this yii\web\View */

/* @var $systemInfo SystemInfoHelper */

use app\assets\FontAwesomeAsset;
use app\utils\SystemInfoHelper;
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
                <?= $systemInfo->hostname ?>
            </h2>
            <p>
                OS:
                <strong><?= $systemInfo->os ?></strong>
            </p>
            <p>
                CPU:
                <strong><?= $systemInfo->cpu ?></strong>
            </p>
            <p>
                RAM:
                <strong><?= $systemInfo->ram ?></strong>
            </p>
            <p>
                Server Time:
                <strong><?= $systemInfo->serverTime ?></strong>
            </p>
            <p>
                Server Up Time:
                <strong><?= $systemInfo->serverUpTime ?></strong>
            </p>
        </div>
        <hr>
        <div>
            <?php if ($systemInfo->osType === 2): ?>
                <div>
                    <h2>
                        <i class="fa-solid fa-bars-progress"></i>
                        Load
                    </h2>
                    <!-- Load Graph -->
                    <p>
                        Load Average: <?= $systemInfo->load ?> (Last 1 min)
                    </p>
                </div>
            <?php endif; ?>
            <div>
                <h2>
                    <i class="fa-solid fa-microchip"></i>
                    CPU
                </h2>
                <!-- CPU Graph -->
                <p>
                    CPU Usage: <?= $systemInfo->cpuUsage ?>%
                </p>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-memory"></i>
                    Memory
                </h2>
                <!-- Memory Graph -->
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
                    <span><?= $systemInfo->dataMountPoint ?></span>
                    <br>
                    File System:
                    <span><?= $systemInfo->mp_fs ?></span>
                    <br>
                    Size:
                    <span><?= $systemInfo->mp_size ?></span>
                    <br>
                    Free:
                    <span><?= $systemInfo->mp_avail ?></span>
                    <br>
                    Used:
                    <span><?= $systemInfo->mp_used ?></span>
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
                <?= $systemInfo->hostname ?>
            </p>
            <p>
                DNS:
                <?= $systemInfo->dns ?>
            </p>
            <p>
                Gateway:
                <?= $systemInfo->gateway ?>
            </p>
            <div>
                <div>
                    <div>
                        <h3><?= $systemInfo->nic['interfaceName'] ?></h3>
                        Status:
                        <span><?= $systemInfo->nic['mac'] ?></span>
                        <br>
                        Speed:
                        <span><?= $systemInfo->nic['speed'] ?></span>
                        <br>
                        IPv4:
                        <span><?= $systemInfo->nic['ipv4'] ?></span>
                        <br>
                        IPv6:
                        <span><?= $systemInfo->nic['ipv6'] ?></span>
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
                <span><?= $systemInfo->users ?></span>
                <br>
                Active (within 24h):
                <span><?= $systemInfo->activeUsers ?></span>
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
                    <span><?= $systemInfo->shares ?></span>
                </div>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-paper-plane"></i>
                    Collection
                </h2>
                <div>
                    Collection:
                    <span><?= $systemInfo->collections ?></span>
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
                    <span><?= $systemInfo->phpVersion ?></span>
                    <br>
                    Memory Limit:
                    <span><?= $systemInfo->memoryLimit ?></span>
                    <br>
                    Max Execution Time:
                    <span><?= $systemInfo->maxExecutionTime ?></span>
                    <br>
                    Upload Max Filesize:
                    <span><?= $systemInfo->uploadMaxFilesize ?></span>
                    <br>
                    Post Max Size:
                    <span><?= $systemInfo->postMaxSize ?></span>
                    <br>
                    Extension:
                    <span><?= $systemInfo->extensions ?></span>
                </div>
            </div>
            <div>
                <h2>
                    <i class="fa-solid fa-database"></i>
                    Database
                </h2>
                <div>
                    Type:
                    <span><?= $systemInfo->dbType ?></span>
                    <br>
                    Version:
                    <span><?= $systemInfo->dbVersion ?></span>
                    <br>
                    Size:
                    <span><?= $systemInfo->dbSize ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
