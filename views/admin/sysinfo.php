 <?php
/* @var $this yii\web\View */

/* @var $systemInfo SystemInfoHelper */

use app\assets\EChartsAsset;
use app\assets\FontAwesomeAsset;
use app\utils\SystemInfoHelper;
use yii\bootstrap5\Html;
use yii\web\JqueryAsset;
use yii\web\View;

FontAwesomeAsset::register($this);
EChartsAsset::register($this);
$this->registerCssFile('@web/css/sysinfo-style.css');
$this->title = '系统信息';
?>

<div class="system-info">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if ($systemInfo->EnableTimeRecords): ?>
        <div>
            <p>Time Records Helper</p>
            <?php
            print_r($systemInfo->timeRecords);
            ?>
        </div>
    <?php endif; ?>

    <div>
        <div>
            <h2>
                <i class="fa-solid fa-server"></i>
                <span id="rdata_hostname"><?= $systemInfo->hostname ?></span>
            </h2>
            <p>
                OS:
                <strong id="rdata_os"><?= $systemInfo->os ?></strong>
            </p>
            <p>
                CPU:
                <strong id="rdata_cpu"><?= $systemInfo->cpu ?></strong>
            </p>
            <p>
                RAM:
                <strong id="rdata_ram"><?= $systemInfo->ram ?></strong>
            </p>
            <p>
                Server Time:
                <strong id="rdata_serverTime"><?= $systemInfo->serverTime ?></strong>
            </p>
            <p>
                Server Up Time:
                <strong id="rdata_serverUpTime"><?= $systemInfo->serverUpTime ?></strong>
            </p>
        </div>
        <hr>
        <div class="row">
            <?php if ($systemInfo->osType === 1): ?>
                <div class="col-xxl-6">
                    <h2>
                        <i class="fa-solid fa-bars-progress"></i>
                        Load
                    </h2>
                    <!-- Load Graph -->
                    <div id="load-graph" style="width: 600px;height:400px;">
                    </div>
                    <p>
                        Load Average: <span id="rdata_load"><?= $systemInfo->load ?></span> (Last 1 min)
                    </p>
                </div>
            <?php endif; ?>
            <div class="col-xxl-6">
                <h2>
                    <i class="fa-solid fa-microchip"></i>
                    CPU
                </h2>
                <!-- CPU Graph -->
                <div id="cpu-graph" style="width: 600px;height:400px;">
                </div>
                <p>
                    CPU Usage: <span id="rdata_cpuUsage"><?= $systemInfo->cpuUsage ?></span>%
                </p>
            </div>
            <div class="col-xxl-6">
                <h2>
                    <i class="fa-solid fa-memory"></i>
                    Memory
                </h2>
                <!-- Memory Graph -->
                <div id="memory-graph" style="width: 600px;height:400px;">
                </div>
                <p>
                    Memory Usage: <span id="rdata_ramUsage"><?= $systemInfo->ramUsage ?></span>%
                </p>
                <p>
                    Swap Usage: <span id="rdata_swapUsage"><?= $systemInfo->swapUsage ?></span>%
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
            <div class="row">
                <div class="row col-md-6 group-content">
                    <!-- disk chart -->
                    <div class="col-xl-6">
                        <div id="disk-chart" style="width: 300px;height:250px;">
                        </div>
                    </div>
                    <div class="col-xl-6" style="padding-top: 50px">
                        <h3>Data</h3>
                        Mount:
                        <span id="rdata_dataMountPoint"><?= $systemInfo->dataMountPoint ?></span>
                        <br>
                        File System:
                        <span id="rdata_mp_fs"><?= $systemInfo->mp_fs ?></span>
                        <br>
                        Size:
                        <span id="rdata_mp_size"><?= $systemInfo->mp_size ?></span>
                        <br>
                        Free:
                        <span id="rdata_mp_avail"><?= $systemInfo->mp_avail ?></span>
                        <br>
                        Used:
                        <span id="rdata_mp_used"><?= $systemInfo->mp_used ?></span> (<span
                                id="rdata_mp_usage"><?= $systemInfo->mp_usage ?></span>%)
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
                    <span id="rdata_hostname2"><?= $systemInfo->hostname ?></span>
                </p>
                <p>
                    DNS:
                    <span id="rdata_dns"><?= $systemInfo->dns ?></span>
                </p>
                <p>
                    Gateway:
                    <span id="rdata_gateway"><?= $systemInfo->gateway ?></span>
                </p>
                <br>
                <div class="row">
                    <div class="group-content col-md-4">
                        <h3 id="rdata_interfaceName"><?= $systemInfo->nic['interfaceName'] ?></h3>
                        MAC:
                        <span id="rdata_mac"><?= $systemInfo->nic['mac'] ?></span>
                        <br>
                        Speed:
                        <span id="rdata_speed"><?= $systemInfo->nic['speed'] ?></span>
                        <br>
                        IPv4:
                        <span id="rdata_ipv4"><?= $systemInfo->nic['ipv4'] ?></span>
                        <br>
                        IPv6:
                        <span id="rdata_ipv6"><?= $systemInfo->nic['ipv6'] ?></span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <h2>
                        <i class="fa-solid fa-users"></i>
                        Active Users
                    </h2>
                    <div class="group-content">
                        All:
                        <span id="rdata_users"><?= $systemInfo->users ?></span>
                        <br>
                        Active (within 24h):
                        <span id="rdata_activeUsers"><?= $systemInfo->activeUsers ?></span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <h2>
                        <i class="fa-solid fa-share-nodes"></i>
                        Share
                    </h2>
                    <div class="group-content">
                        Link:
                        <span id="rdata_shares"><?= $systemInfo->shares ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <h2>
                        <i class="fa-solid fa-paper-plane"></i>
                        Collection
                    </h2>
                    <div class="group-content">
                        Link:
                        <span id="rdata_collections"><?= $systemInfo->collections ?></span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h2>
                        <i class="fa-brands fa-php"></i>
                        PHP
                    </h2>
                    <div class="group-content">
                        Version:
                        <span id="rdata_phpVersion"><?= $systemInfo->phpVersion ?></span>
                        <br>
                        Memory Limit:
                        <span id="rdata_memoryLimit"><?= $systemInfo->memoryLimit ?></span>
                        <br>
                        Max Execution Time:
                        <span id="rdata_maxExecutionTime"><?= $systemInfo->maxExecutionTime ?>s</span>
                        <br>
                        Upload Max Filesize:
                        <span id="rdata_uploadMaxFilesize"><?= $systemInfo->uploadMaxFilesize ?></span>
                        <br>
                        Post Max Size:
                        <span id="rdata_postMaxSize"><?= $systemInfo->postMaxSize ?></span>
                        <br>
                        Extension:
                        <span id="rdata_extensions"><?= $systemInfo->extensions ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2>
                        <i class="fa-solid fa-database"></i>
                        Database
                    </h2>
                    <div class="group-content">
                        Type:
                        <span id="rdata_dbType"><?= $systemInfo->dbType ?></span>
                        <br>
                        Version:
                        <span id="rdata_dbVersion"><?= $systemInfo->dbVersion ?></span>
                        <br>
                        Size:
                        <span id="rdata_dbSize"><?= $systemInfo->dbSize ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $dataTime = substr($systemInfo->serverTime, 11, 8);
    $mp_free = round(100 - $systemInfo->mp_usage, 2);
    $script = <<< JS
var myChart = echarts.init(document.getElementById('cpu-graph'));
var myChart2 = echarts.init(document.getElementById('memory-graph'));
var myChart3 = echarts.init(document.getElementById('disk-chart'));
// check load-graph is exist
var needLoadGraph = document.getElementById('load-graph');
// var 
var option = {
    legend: {
        data: ['CPU']
    },
    xAxis: {
        type: 'category',
        boundaryGap: false,
        data: ["$dataTime"]
    },
    yAxis: {
        type: 'value',
        min: 0,
        max: 100
    },
    series: [
        {
            'name': 'CPU',
            data: [$systemInfo->cpuUsage],
            label: {
                show: true,
                position: 'top',
                formatter: '{c}%'
            },
            emphasis: {
                label: {
                    scale: 1.5
                }
            },
            type: 'line',
            smooth: true,
            areaStyle: {}
        }
    ]
};
if(needLoadGraph !== null){
    var myChart1 = echarts.init(needLoadGraph);
    var option1 = {
        legend: {
            data: ['Load']
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ["$dataTime"]
        },
        yAxis: {
            type: 'value',
            min: 0
            // max: 100
        },
        series: [
            {
                'name': 'Load',
                data: [$systemInfo->load],
                label: {
                    show: true,
                    position: 'top',
                    formatter: '{c}'
                },
                emphasis: {
                    label: {
                        scale: 1.5
                    }
                },
                type: 'line',
                smooth: true,
                areaStyle: {}
            }
        ]
    };
    myChart1.setOption(option1);
}
var option2 = {
    legend: {
        data: ['RAM', 'SWAP']
    },
    xAxis: {
        type: 'category',
        boundaryGap: false,
        data: ["$dataTime"]
    },
    yAxis: {
        type: 'value',
        min: 0,
        max: 100
    },
    series: [
        {
            name: 'RAM',
            data: [$systemInfo->ramUsage],
            type: 'line',
            areaStyle: {},
            smooth: true,
            lineStyle: {
                color: 'red'
            },
            label: {
                show: true,
                position: 'top',
                formatter: '{c}%',
            },
            emphasis: {
                label: {
                    scale: 1.5
                }
            }
        },
        {
            name: 'SWAP',
            data: [$systemInfo->swapUsage],
            type: 'line',
            areaStyle: {},
            smooth: true,
            lineStyle: {
                color: 'blue'
            },
            label: {
                show: true,
                position: 'bottom',
                formatter: '{c}%'
            },
            emphasis: {
                label: {
                    scale: 1.5
                }
            },
        }
    ]
};
var option3 = {
    tooltip: {
        trigger: 'item',
        formatter: function(params) {
            return params.name + ': ' + params.value + '%';
        }
    },
    legend: {
        top: '5%',
        left: 'center'
    },
    series: [
        {
            name: 'Data',
            type: 'pie',
            radius: ['40%', '70%'],
            avoidLabelOverlap: false,
            padAngle: 5,
            itemStyle: {
                borderRadius: 10
            },
            label: {
                show: false,
                position: 'center'
            },
            labelLine: {
                show: false
            },
            data: [
                { value: $systemInfo->mp_usage, name: 'Used Space' },
                { value: $mp_free, name: 'Free Space' }
            ]
        }
    ]
};
myChart.setOption(option);
myChart2.setOption(option2);
myChart3.setOption(option3);
JS;
    $this->registerJs($script, View::POS_END);
    $this->registerJsFile('@web/js/sysinfo-core.js', ['depends' => [JqueryAsset::class, EChartsAsset::class], 'position' => View::POS_END]);
    ?>
