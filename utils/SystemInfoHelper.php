<?php

namespace app\utils;

use app\models\CollectionTasks;
use app\models\Share;
use app\models\User;
use DateTime;
use Yii;
use yii\db\Exception;

/**
 * Class SystemInfoHelper
 * Get system information
 * Supported OS: Windows, Linux
 * Windows needs PowerShell
 * Linux needs ethtool
 */
class SystemInfoHelper
{
    public array $timeRecords; // Time records
    public bool $EnableTimeRecords = false; // Enable time records
    public string $hostname; // Hostname
    public int $osType; // 0: Windows, 1: Linux, 2: Others
    public string $os; // OS
    public string $cpu; // CPU
    public int $ram; // RAM Byte
    public string $serverTime; // Server Time
    public string $serverUpTime; // Server Up Time
    public float $load; // Load
    public float $cpuUsage; // CPU Usage
    public float $ramUsage; // RAM Usage
    public string $dataMountPoint; // Data Mount Point
    public string $mp_fs; // Filesystem
    public int $mp_size; // Size
    public int $mp_used; // Used
    public int $mp_avail; // Available
    public string $dns; // DNS
    public string $gateway; // Gateway
    public array $nic; // Network Interface [interfaceName, mac, speed, ipv4, ipv6]
    public int $users; // Users
    public int $activeUsers; // Active Users
    public int $shares; // Shares
    public int $collections; // Collections
    public string $phpVersion; // PHP Version
    public string $memoryLimit; // Memory Limit
    public string $maxExecutionTime; // Max Execution Time
    public string $uploadMaxFilesize; // Upload Max Filesize
    public string $postMaxSize; // Post Max Size
    public string $extensions; // Extensions
    public string $dbType; // Database Type
    public string $dbVersion; // Database Version
    public int $dbSize; // Database Size

    /**
     * 检查操作系统类型
     * 必须在其他检测前调用
     * @return void
     */
    private function detectOsType(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $os = php_uname();
        if (stripos($os, 'windows') !== false) {
            $this->osType = 0;
        } elseif (stripos($os, 'linux') !== false) {
            $this->osType = 1;
        } else {
            $this->osType = 2;
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectOsType'] = microtime(true) - $start;
        }
    }

    /**
     * 获取主机名
     * @return void
     */
    private function detectHostname(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->hostname = gethostname();
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectHostname'] = microtime(true) - $start;
        }
    }

    /**
     * 获取操作系统信息
     * @return void
     */
    private function detectOs(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->os = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectOs'] = microtime(true) - $start;
        }
    }

    /**
     * 获取CPU信息
     * @return void
     */
    private function detectCpu(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $this->cpu = shell_exec('powershell "$processorInfo = Get-WmiObject Win32_Processor;$processorInfo.Name + \' (\' + $processorInfo.NumberOfCores + \' Cores)\'"');
        } else {
            $cpu_model = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | uniq | awk -F': ' '{print $2}'"));
            $cpu_cores = shell_exec('nproc');
            $this->cpu = $cpu_model . ' (' . $cpu_cores . ' Cores)';
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectCpu'] = microtime(true) - $start;
        }
    }

    /**
     * 获取已安装RAM信息
     * 32位系统最大只会获取到2147483647结果 (2GB)
     * @return void
     */
    private function detectRam(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $this->ram = intval(shell_exec('powershell (Get-WmiObject Win32_computerSystem).TotalPhysicalMemory'));
        } else {
            $this->ram = intval(shell_exec("grep MemTotal /proc/meminfo | awk '{print $2}'"));
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectRam'] = microtime(true) - $start;
        }
    }

    /**
     * 获取服务器时间
     * @return void
     */
    private function detectServerTime(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->serverTime = date('Y-m-d H:i:s T');
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectServerTime'] = microtime(true) - $start;
        }
    }

    /**
     * 获取服务器已运行时间
     * @return void
     */
    private function detectServerUptime(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $lastBootUpTime = strtotime(shell_exec('powershell (Get-CimInstance Win32_OperatingSystem).LastBootUpTime'));
            $now = new DateTime();
            $bootTime = new DateTime("@$lastBootUpTime");
            $interval = $bootTime->diff($now);
            $this->serverUpTime = $interval->format('%a days, %h hours, %i minutes, %s seconds');
        } else {
            $this->serverUpTime = str_replace("up ", "", trim(shell_exec('uptime -p')));
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectServerUptime'] = microtime(true) - $start;
        }
    }

    /**
     * 获取服务器负载
     * Only for Linux
     * @return void
     */
    private function detectLoad(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->load = sys_getloadavg()[0];
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectLoad'] = microtime(true) - $start;
        }
    }

    /**
     * 获取CPU使用率
     * @return void
     */
    private function detectCpuUsage(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $this->cpuUsage = floatval(shell_exec('powershell (Get-WmiObject Win32_Processor).LoadPercentage'));
        } else {
            $this->cpuUsage = floatval(shell_exec("top -b -n1 | grep 'Cpu(s)' | awk '{print $2 + $4}'"));
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectCpuUsage'] = microtime(true) - $start;
        }
    }

    /**
     * 获取RAM使用率
     * @return void
     */
    private function detectRamUsage(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $this->ramUsage = round(floatval(shell_exec('powershell "100 - (Get-WmiObject Win32_OperatingSystem).FreePhysicalMemory / (Get-WmiObject Win32_Operati
ngSystem).TotalVisibleMemorySize * 100"')), 2);
        } else {
            $this->ramUsage = round(floatval(shell_exec('free | grep Mem | awk \'{print $3/$2 * 100.0}\'')), 2);
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectRamUsage'] = microtime(true) - $start;
        }
    }

    /**
     * 获取数据挂载点信息
     * 包含文件系统、大小、已用、可用
     * @return void
     */
    private function detectDataMountPoint(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $dataPath = Yii::getAlias(Yii::$app->params['dataDirectory']);
        if ($this->osType === 0) {
            $this->dataMountPoint = shell_exec('powershell (Get-Item \"' . $dataPath . '\").PSDrive.Root'); // X:\
            $dmp = substr($this->dataMountPoint, 0, 2); // X:
            $this->mp_fs = shell_exec('powershell "(Get-WmiObject Win32_Volume | Where-Object {$_.DriveLetter -eq \'' . $dmp . '\'}).FileSystem"');
            $this->mp_size = intval(shell_exec('powershell "(Get-WmiObject Win32_LogicalDisk | Where-Object {$_.DeviceID -eq \'' . $dmp . '\'}).Size"'));
            $this->mp_avail = intval(shell_exec('powershell "(Get-WmiObject Win32_LogicalDisk | Where-Object {$_.DeviceID -eq \'' . $dmp . '\'}).FreeSpace"'));
            $this->mp_used = $this->mp_size - $this->mp_avail;
        } else {
            $this->dataMountPoint = shell_exec("df -P \"" . $dataPath . "\" | awk 'NR==2{print $6}'");
            $this->mp_fs = shell_exec("df -T \"" . $this->dataMountPoint . "\" | awk 'NR==2{print $2}'");
            $this->mp_size = intval(shell_exec('df -k "' . $this->dataMountPoint . '" | awk \'NR==2{print $2}\''));
            $this->mp_used = intval(shell_exec('df -k "' . $this->dataMountPoint . '" | awk \'NR==2{print $3}\''));
            $this->mp_avail = intval(shell_exec('df -k "' . $this->dataMountPoint . '" | awk \'NR==2{print $4}\''));
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectDataMountPoint'] = microtime(true) - $start;
        }
    }

    /**
     * 获取DNS信息
     * @return void
     */
    private function detectDns(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $this->dns = shell_exec('powershell "Get-CimInstance Win32_NetworkAdapterConfiguration | Select-Object -ExpandProperty DNSServerSearchOrder"');
        } else {
            $this->dns = shell_exec('cat /etc/resolv.conf | grep nameserver | awk \'{print $2}\' | tr \'\\n\' \',\' | sed \'s/,$//\'');
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectDns'] = microtime(true) - $start;
        }
    }

    /**
     * 获取网关信息
     * @return void
     */
    private function detectGateway(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $this->gateway = shell_exec('powershell "Get-CimInstance Win32_NetworkAdapterConfiguration | Select-Object -ExpandProperty DefaultIPGateway"');
        } else {
            $this->gateway = shell_exec('ip route | grep default | awk \'{print $3}\'');
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectGateway'] = microtime(true) - $start;
        }
    }

    /**
     * 获取网卡信息
     * 包含接口名、MAC地址、速度、IPv4、IPv6
     * @return void
     */
    private function detectNic(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        if ($this->osType === 0) {
            $result1 = shell_exec('powershell "$NetAdapters = Get-NetAdapter | Where-Object { $_.Status -eq \'Up\' };$Names = $NetAdapters.Name -join \', \';$MacAddresses = $NetAdapters.MacAddress -join \', \';$Speed = $NetAdapters.LinkSpeed -join \', \';Write-Output $Names;Write-Output $MacAddresses;Write-Output $Speed;"');
            $lines = explode("\n", $result1);
            $result2 = shell_exec('powershell "$IPAddressList = (Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration | Where-Object { $_.IPAddress -ne $null }).IPAddress;$IPv4Addresses = @();$IPv6Addresses = @();foreach ($IPAddress in $IPAddressList) {if ($IPAddress -match \'\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\') {$IPv4Addresses += $IPAddress} elseif ($IPAddress -match \'[:0-9A-Fa-f]{4,}:[0-9A-Fa-f:]+\') {$IPv6Addresses += $IPAddress}};Write-Output $($IPv4Addresses -join \', \');Write-Output $($IPv6Addresses -join \', \');"');
            $lines2 = explode("\n", $result2);
            $this->nic = [
                'interfaceName' => $lines[0],
                'mac' => strtolower($lines[1]),
                'speed' => $lines[2],
                'ipv4' => $lines2[0],
                'ipv6' => $lines2[1]
            ];
        } else {
            $name = trim(shell_exec('ip link | awk -F: \'$0 !~ "lo|vir|wl|^[^0-9]"{print $2;getline}\''));
            $this->nic = [
                'interfaceName' => $name,
                'mac' => shell_exec("ip addr show $name | awk '/ether/ {print $2}'"),
                'speed' => shell_exec("ethtool $name 2>/dev/null | awk '/Speed:/ {print $2}'"),
                'ipv4' => shell_exec("ip addr show $name | awk '/inet / {print $2}' | cut -d'/' -f1"),
                'ipv6' => shell_exec("ip addr show $name | awk '/inet6 / {print $2}' | cut -d'/' -f1")
            ];
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectNic'] = microtime(true) - $start;
        }
    }

    /**
     * 获取用户信息
     * 包含用户数、活跃用户数
     * @return void
     */
    private function detectUsers(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->users = User::find()->count();
        $activeTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $this->activeUsers = User::find()->where(['>', 'last_login', $activeTime])->count();
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectUsers'] = microtime(true) - $start;
        }
    }

    /**
     * 获取分享信息
     * @return void
     */
    private function detectShares(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->shares = Share::find()->count();
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectShares'] = microtime(true) - $start;
        }
    }

    /**
     * 获取收集任务信息
     * @return void
     */
    private function detectCollections(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->collections = CollectionTasks::find()->count();
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectCollections'] = microtime(true) - $start;
        }
    }

    /**
     * 获取PHP环境信息
     * @return void
     */
    private function detectEnv(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->phpVersion = phpversion();
        $this->memoryLimit = ini_get('memory_limit');
        $this->maxExecutionTime = ini_get('max_execution_time');
        $this->uploadMaxFilesize = ini_get('upload_max_filesize');
        $this->postMaxSize = ini_get('post_max_size');
        $this->extensions = implode(', ', get_loaded_extensions());
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectEnv'] = microtime(true) - $start;
        }
    }

    /**
     * 获取数据库信息
     * @return void
     */
    private function detectDb(): void
    {
        $start = null;
        if ($this->EnableTimeRecords) {
            $start = microtime(true);
        }
        $this->dbType = Yii::$app->db->driverName;
//        debug
//        $this->dbVersion = '1';
//        $this->dbSize = 1;
//        return;
        try {
            $this->dbVersion = Yii::$app->db->createCommand('SELECT VERSION()')->queryScalar();
        } catch (Exception) {
            $this->dbVersion = 'Fetch Error';
        }
        $dbName = $_ENV['DB_NAME'];
        try {
            $this->dbSize = Yii::$app->db->createCommand("select SUM(data_length + index_length) from information_schema.TABLES where table_schema = '$dbName' group by table_schema;")->queryScalar();
        } catch (Exception) {
            $this->dbSize = 'Fetch Error';
        }
        if ($this->EnableTimeRecords) {
            $this->timeRecords['detectDb'] = microtime(true) - $start;
        }
    }

    /**
     * 获取系统信息(初始化)
     * @return SystemInfoHelper
     */
    public static function getSysInfoInit(): SystemInfoHelper
    {
        $sysInfo = new SystemInfoHelper();
        // Time records (Debug)
        $sysInfo->EnableTimeRecords = true;

        $sysInfo->detectOsType();
        $sysInfo->detectHostname();
        $sysInfo->detectOs();
        $sysInfo->detectCpu();
        $sysInfo->detectRam();
        $sysInfo->detectServerTime();
        $sysInfo->detectServerUptime();

        if ($sysInfo->osType === 1) {
            $sysInfo->detectLoad();

        } else {
            $sysInfo->load = -1;
        }
        $sysInfo->detectCpuUsage();
        $sysInfo->detectRamUsage();
        $sysInfo->detectDataMountPoint();
        $sysInfo->detectDns();
        $sysInfo->detectGateway();
        $sysInfo->detectNic();
        $sysInfo->detectUsers();
        $sysInfo->detectShares();
        $sysInfo->detectCollections();
        $sysInfo->detectEnv();
        $sysInfo->detectDb();
        return $sysInfo;

    }

    /**
     * 获取系统信息(刷新)
     * 为了减少资源消耗，只刷新部分数据
     * @return SystemInfoHelper
     */
    public static function getSysInfoFre(): SystemInfoHelper
    {
        $sysInfo = new SystemInfoHelper();
        $sysInfo->detectServerTime();
        $sysInfo->detectServerUptime();
        if ($sysInfo->osType === 1) {
            $sysInfo->detectLoad();
        } else {
            $sysInfo->load = -1;
        }
        $sysInfo->detectCpuUsage();
        $sysInfo->detectRamUsage();
        $sysInfo->detectDataMountPoint();
        return $sysInfo;
    }

}