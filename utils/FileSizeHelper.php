<?php

namespace app\utils;

use app\models\User;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\web\NotFoundHttpException;

class FileSizeHelper
{
    /**
     * 计算指定目录的大小
     *
     * @param string $directory 目录路径
     * @return int 目录的大小（字节）
     */
    public static function getDirectorySize(string $directory): int
    {
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
     * @return bool
     */
    public static function hasEnoughSpace(int $file_size = 0, int $user_id = null): bool
    {
        if ($user_id === null) {
            $user_id = Yii::$app->user->id;
        }
        $userHomeDir = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $user_id;
        $userHomeDirSize_MB = self::getDirectorySize($userHomeDir) / 1024 / 1024;
        $file_size_MB = $file_size / 1024 / 1024;
        $user = User::findOne($user_id);
        if ($user === null) {
            throw new NotFoundHttpException('User not found.');
        }
        $limit = $user->storage_limit;
        if ($limit == -1) {
            return true;
        }
        return $userHomeDirSize_MB + $file_size_MB <= $limit;
    }

    /**
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
     * @param $megabytes
     * @param $precision
     * @return string
     */
    public static function formatMegaBytes($megabytes, $precision = 2): string
    {
        if ($megabytes === -1) {
            return '∞';
        }
        $bytes = $megabytes * pow(1024, 2); // Convert megabytes to bytes
        return self::formatBytes($bytes, $precision);
    }
}