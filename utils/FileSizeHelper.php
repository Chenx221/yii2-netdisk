<?php

namespace app\utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
        if($megabytes===-1){
            return '∞';
        }
        $bytes = $megabytes * pow(1024, 2); // Convert megabytes to bytes
        return self::formatBytes($bytes, $precision);
    }
}