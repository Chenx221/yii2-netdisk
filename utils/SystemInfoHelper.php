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
        $os = php_uname();
        if (stripos($os, 'windows') !== false) {
            $this->osType = 0;
        } elseif (stripos($os, 'linux') !== false) {
            $this->osType = 1;
        } else {
            $this->osType = 2;
        }
    }

    /**
     * 获取主机名
     * @return void
     */
    private function detectHostname(): void
    {
        $this->hostname = gethostname();
    }

    /**
     * 获取操作系统信息
     * @return void
     */
    private function detectOs(): void
    {
        $this->os = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');
    }

    /**
     * 获取CPU信息
     * @return void
     */
    private function detectCpu(): void
    {
        if ($this->osType === 0) {
            $cpu_model = shell_exec('powershell (Get-WmiObject Win32_Processor).Name');
            $cpu_cores = shell_exec('powershell (Get-WmiObject Win32_Processor).NumberOfCores');
        } else {
            $cpu_model = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | uniq | awk -F': ' '{print $2}'"));
            $cpu_cores = shell_exec('nproc');
        }
        $this->cpu = $cpu_model . ' (' . $cpu_cores . ' Cores)';
    }

    /**
     * 获取已安装RAM信息
     * 32位系统最大只会获取到2147483647结果 (2GB)
     * @return void
     */
    private function detectRam(): void
    {
        if ($this->osType === 0) {
            $this->ram = intval(shell_exec('powershell (Get-WmiObject Win32_computerSystem).TotalPhysicalMemory'));
        } else {
            $this->ram = intval(shell_exec("grep MemTotal /proc/meminfo | awk '{print $2}'"));
        }
    }

    /**
     * 获取服务器时间
     * @return void
     */
    private function detectServerTime(): void
    {
        $this->serverTime = date('Y-m-d H:i:s T');
    }

    /**
     * 获取服务器已运行时间
     * @return void
     */
    private function detectServerUptime(): void
    {
        if ($this->osType === 0) {
            $lastBootUpTime = strtotime(shell_exec('powershell (Get-CimInstance Win32_OperatingSystem).LastBootUpTime'));
            $now = new DateTime();
            $bootTime = new DateTime("@$lastBootUpTime");
            $interval = $bootTime->diff($now);
            $this->serverUpTime = $interval->format('%a days, %h hours, %i minutes, %s seconds');
        } else {
            $this->serverUpTime = str_replace("up ", "", trim(shell_exec('uptime -p')));
        }
    }

    /**
     * 获取服务器负载
     * Only for Linux
     * @return void
     */
    private function detectLoad(): void
    {
        $this->load = sys_getloadavg()[0];
    }

    /**
     * 获取CPU使用率
     * @return void
     */
    private function detectCpuUsage(): void
    {
        if ($this->osType === 0) {
            $this->cpuUsage = floatval(shell_exec('powershell (Get-WmiObject Win32_Processor).LoadPercentage'));
        } else {
            $this->cpuUsage = floatval(shell_exec("top -b -n1 | grep 'Cpu(s)' | awk '{print $2 + $4}'"));
        }
    }

    /**
     * 获取RAM使用率
     * @return void
     */
    private function detectRamUsage(): void
    {
        if ($this->osType === 0) {
            $this->ramUsage = round(floatval(shell_exec('powershell "100 - (Get-WmiObject Win32_OperatingSystem).FreePhysicalMemory / (Get-WmiObject Win32_Operati
ngSystem).TotalVisibleMemorySize * 100"')), 2);
        } else {
            $this->ramUsage = round(floatval(shell_exec('free | grep Mem | awk \'{print $3/$2 * 100.0}\'')), 2);
        }
    }

    /**
     * 获取数据挂载点信息
     * 包含文件系统、大小、已用、可用
     * @return void
     */
    private function detectDataMountPoint(): void
    {
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
    }

    /**
     * 获取DNS信息
     * @return void
     */
    private function detectDns(): void
    {
        if ($this->osType === 0) {
            $this->dns = shell_exec('powershell "((Get-NetIPConfiguration | Where-Object { $_.NetAdapter.Status -eq \'Up\' }).DnsServer.ServerAddresses) -join \',\'"');
        } else {
            $this->dns = shell_exec('cat /etc/resolv.conf | grep nameserver | awk \'{print $2}\' | tr \'\\n\' \',\' | sed \'s/,$//\'');
        }
    }

    /**
     * 获取网关信息
     * @return void
     */
    private function detectGateway(): void
    {
        if ($this->osType === 0) {
            $this->gateway = shell_exec('powershell "(Get-NetIPConfiguration | Where-Object { $_.NetAdapter.Status -eq \'Up\' }).IPv4DefaultGateway.NextHop"');
        } else {
            $this->gateway = shell_exec('ip route | grep default | awk \'{print $3}\'');
        }
    }

    /**
     * 获取网卡信息
     * 包含接口名、MAC地址、速度、IPv4、IPv6
     * @return void
     */
    private function detectNic(): void
    {
        if ($this->osType === 0) {
            $this->nic = [
                'interfaceName' => shell_exec('powershell "(Get-NetAdapter | Where-Object { $_.Status -eq \'Up\' }).Name"'),
                'mac' => strtolower(shell_exec('powershell "(Get-NetAdapter | Where-Object { $_.Status -eq \'Up\' }).MacAddress"')),
                'speed' => shell_exec('powershell "(Get-NetAdapter | Where-Object { $_.Status -eq \'Up\' }).LinkSpeed"'),
                'ipv4' => shell_exec('powershell "(Get-NetIPConfiguration | Where-Object { $_.NetAdapter.Status -eq \'Up\' }).IPv4Address.IPAddress"'),
                'ipv6' => shell_exec('powershell "(Get-NetIPConfiguration | Where-Object { $_.NetAdapter.Status -eq \'Up\' }).IPv6Address.IPAddress"')
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
    }

    /**
     * 获取用户信息
     * 包含用户数、活跃用户数
     * @return void
     */
    private function detectUsers(): void
    {
        $this->users = User::find()->count();
        $activeTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $this->activeUsers = User::find()->where(['>', 'last_login', $activeTime])->count();
    }

    /**
     * 获取分享信息
     * @return void
     */
    private function detectShares(): void
    {
        $this->shares = Share::find()->count();
    }

    /**
     * 获取收集任务信息
     * @return void
     */
    private function detectCollections(): void
    {
        $this->collections = CollectionTasks::find()->count();
    }

    /**
     * 获取PHP环境信息
     * @return void
     */
    private function detectEnv(): void
    {
        $this->phpVersion = phpversion();
        $this->memoryLimit = ini_get('memory_limit');
        $this->maxExecutionTime = ini_get('max_execution_time');
        $this->uploadMaxFilesize = ini_get('upload_max_filesize');
        $this->postMaxSize = ini_get('post_max_size');
        $this->extensions = implode(', ', get_loaded_extensions());
    }

    /**
     * 获取数据库信息
     * @return void
     */
    private function detectDb(): void
    {
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
    }

    /**
     * 获取系统信息(初始化)
     * @return SystemInfoHelper
     */
    public static function getSysInfoInit(): SystemInfoHelper
    {
        $sysInfo = new SystemInfoHelper();
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