<?php

namespace app\utils;

use DateTime;
use Yii;

/**
 * Class SystemInfoHelper
 * Get system information
 * Supported OS: Windows, Linux
 * Windows needs PowerShell
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
    public string $mp_used; // Used
    public string $mp_avail; // Available
    public string $dns; // DNS
    public string $gateway; // Gateway
    public array $nics; // Network Interfaces
    public array $nicsData; // Network Interfaces Data
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
    public string $dbSize; // Database Size


    /**
     * SystemInfoHelper constructor.
     * Detect the OS type before the class is initialized.
     */
    public function __construct()
    {
        $this->osType = $this->detectOsType();
    }

    /**
     * @return int
     */
    private function detectOsType(): int
    {
        $os = php_uname();
        if (stripos($os, 'windows') !== false) {
            return 0;
        } elseif (stripos($os, 'linux') !== false) {
            return 1;
        } else {
            return 2;
        }
    }

    private function detectHostname(): void
    {
        $this->hostname = gethostname();
    }

    private function detectOs(): void
    {
        $this->os = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');
    }

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
     * 32位系统最大只会获取到2147483647结果
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

    private function detectServerTime(): void
    {
        $this->serverTime = date('Y-m-d H:i:s T');
    }

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
     * Only for Linux
     * @return void
     */
    private function detectLoad(): void
    {
        $this->load = sys_getloadavg()[0];
    }

    private function detectCpuUsage(): void
    {
        if ($this->osType === 0) {
            $this->cpuUsage = floatval(shell_exec('powershell (Get-WmiObject Win32_Processor).LoadPercentage'));
        } else {
            $this->cpuUsage = floatval(shell_exec("top -b -n1 | grep 'Cpu(s)' | awk '{print $2 + $4}'"));
        }
    }

    private function detectRamUsage(): void
    {
        if ($this->osType === 0) {
            $this->ramUsage = round(floatval(shell_exec('powershell "100 - (Get-WmiObject Win32_OperatingSystem).FreePhysicalMemory / (Get-WmiObject Win32_Operati
ngSystem).TotalVisibleMemorySize * 100"')), 2);
        } else {
            $this->ramUsage = round(floatval(shell_exec('free | grep Mem | awk \'{print $3/$2 * 100.0}\'')), 2);
        }
    }

    private function detectDataMountPoint(): void
    {
        $dataPath = Yii::getAlias(Yii::$app->params['dataDirectory']);
        if ($this->osType === 0) {
            $this->dataMountPoint = shell_exec('powershell (Get-Item \"' . $dataPath . '\").PSDrive.Root');
            $dmp = substr($this->dataMountPoint, 0, 2);
            $this->mp_fs = shell_exec('powershell "(Get-WmiObject Win32_Volume | Where-Object {$_.DriveLetter -eq \'' . $dmp . '\'}).FileSystem"');
            $this->mp_size = shell_exec('powershell "(Get-WmiObject Win32_LogicalDisk | Where-Object {$_.DeviceID -eq \'' . $dmp . '\'}).size"');
        } else {
            $this->dataMountPoint = shell_exec("df -P \"" . $dataPath . "\" | awk 'NR==2{print $6}'");
            $this->mp_fs = shell_exec("df -T \"" . $this->dataMountPoint . "\" | awk 'NR==2{print $2}'");
            $this->mp_size = shell_exec('df -k "' . $this->dataMountPoint . '" | awk \'NR==2{print $2}\'');
        }
    }

    private function detectMpUsed(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectMpAvail(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectDns(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectGateway(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectNics(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectUsers(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectShares(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectCollections(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectEnv(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    private function detectDb(): void
    {
        if ($this->osType === 0) {

        } else {
        }
    }

    public static function getSysInfo()
    {

    }

}