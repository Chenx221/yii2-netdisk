<?php

namespace app\utils;

use app\models\User;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;

class FileSizeHelper
{
    /**
     * 计算指定目录的大小
     * 返回大小单位为字节
     * @param string $directory 目录路径
     * @return int 目录的大小（字节）
     */
    public static function getDirectorySize(string $directory): int
    {
        if (!is_dir($directory)) {
            return 0;
        }
        $size = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    /**
     * 判断用户home是否有足够的容量存放文件
     * @param int $file_size 文件大小B(可选,如果文件已经添加到网盘时，不需要这个参数)
     * @param int|null $user_id
     * @return bool
     */
    public static function hasEnoughSpace(int $file_size = 0, int $user_id = null): bool
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        $userHomeDirSize = self::getUserHomeDirSize($user_id);
        $userHomeDirSize_MB = $userHomeDirSize / 1024 / 1024;
        $userVaultDirSize = self::getUserVaultDirSize($user_id);
        $userVaultDirSize_MB = $userVaultDirSize / 1024 / 1024;

        $file_size_MB = $file_size / 1024 / 1024; // 即将新增的文件

        $user = User::findOne($user_id);
        $limit = $user->storage_limit;
        if ($limit == -1) {
            return true;
        }

        return $userHomeDirSize_MB + $userVaultDirSize_MB + $file_size_MB <= $limit;
    }

    /**
     * 获取用户home目录的大小
     * @param int|null $user_id
     * @return int
     */
    public static function getUserHomeDirSize(int $user_id = null): int
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        $userHomeDir = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $user_id;
        return self::getDirectorySize($userHomeDir);
    }

    /**
     * 获取用户vault目录的大小
     * @param int|null $user_id
     * @return int
     */
    public static function getUserVaultDirSize(int $user_id = null): int
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        $userHomeDir = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $user_id . '.secret';
        return self::getDirectorySize($userHomeDir);
    }

    /**
     * 获取用户所有目录的大小
     * @param int|null $user_id
     * @return int
     */
    public static function getUserAllDirSize(int $user_id = null): int
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        return self::getUserHomeDirSize($user_id) + self::getUserVaultDirSize($user_id);
    }

    /**
     * 获取用户home目录的大小
     * 返回格式化后的大小(字符串)
     * @param int|null $user_id
     * @return string
     */
    public static function getFormatUserAllDirSize(int $user_id = null): string
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        return self::formatBytes(self::getUserAllDirSize($user_id));
    }

    /**
     * 格式化文件大小(单位转换)
     * 传入Byte
     * @param $bytes
     * @param $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * 格式化文件大小(单位转换)
     * 传入MB
     * @param $megabytes
     * @param int $precision
     * @return string
     */
    public static function formatMegaBytes($megabytes, int $precision = 2): string
    {
        if ($megabytes === -1) {
            return '∞';
        }
        $bytes = $megabytes * pow(1024, 2); // Convert megabytes to bytes
        return self::formatBytes($bytes, $precision);
    }

    /**
     * 获取用户已使用的容量百分比
     * @param $user_id
     * @return string
     */
    public static function getUsedPercent($user_id = null): string
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        $user = User::findOne($user_id);
        $limit = $user->storage_limit * pow(1024, 2);
        if ($limit == -1) {
            return '∞';
        }
        $userAllDirSize = self::getUserAllDirSize($user_id);
        return round($userAllDirSize / $limit * 100, 2) . ' %';
    }
}