<?php

namespace app\utils;

class FileTypeDetector
{

    /**
     * Detects the file type based on the file extension and returns the corresponding Font Awesome icon class.
     * Be careful to use this method, as it does not check the file content.
     * And match is a new feature in PHP 8.0, so you need to upgrade your PHP version to 8.0 or higher to use it.
     * 对着wikipedia的文件扩展名列表写的，可能有些不准确,反正是累死了...
     *
     * @param $filePath
     * @return string
     */
    public static function detect($filePath): string
    {
        if (is_dir($filePath)) {
            return 'fa-regular fa-folder';
        } else {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            return match ($extension) {
                'txt', 'md', 'xml', 'xps', 'markdown', 'rss', 'ini', 'json', 'yaml', 'log', 'text', 'asc' => 'fa-regular fa-file-lines',
                'pdf' => 'fa-regular fa-file-pdf',
                'doc', 'docx', 'docm', 'dot', 'dotx', 'odm', 'odt', 'ott', 'wps' => 'fa-regular fa-file-word',
                'xls', 'xlsx', 'xlk', 'xlsb', 'xlsm', 'xlt', 'xltm', 'xlw' => 'fa-regular fa-file-excel',
                'csv' => 'fa-regular fa-file-csv',
                'ppt', 'pptx' => 'fa-regular fa-file-powerpoint',
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'psd', 'clip', 'raw', 'tiff', 'tif' => 'fa-regular fa-file-image',
                'zip', 'rar', '7z', 'tar', 'tar.gz', 'xz', 'cab' => 'fa-regular fa-file-zipper',
                'mp3', 'wav', 'flac', 'acc', 'ogg', 'wma', 'ac3' => 'fa-regular fa-file-audio',
                'mp4', 'mkv', '3gp', 'avi', 'flv', 'm4v', 'mov', 'mpeg', 'mpg', 'mpe', 'rm', 'wmv', 'webm' => 'fa-regular fa-file-video',
                'xhtml', 'asp', 'aspx', 'cgi', 'jsp', 'pl', 'phtml', 'php', 'html', 'htm', 'css', 'c', 'cpp', 'py', 'cs', 'ahk', 'as', 'au3', 'bat', 'fs', 'go', 'ipynb', 'kt', 'lua', 'o', 'ps1', 'ps1xml', 'psc1', 'psd1', 'psm1', 'pyc', 'pyo', 'r', 'rb', 'vbs', 'vb', 'cls', 'cc', 'cxx', 'cbp', 'csproj', 'h', 'hpp', 'hxx', 'vbg', 'vbp', 'vip', 'vbproj', 'vcproj', 'vdproj' => 'fa-regular fa-file-code',
                'abb', 'apk' => 'fa-brands fa-android',
                'exe', 'appx', 'esd', 'wim', 'msi', 'cpl', 'com', 'dll' => 'fa-brands fa-windows',
                'ass', 'srt' => 'fa-regular fa-closed-captioning',
                'deb' => 'fa-brands fa-debian',
                'java', 'jar', 'class' => 'fa-brands fa-java',
                'js' => 'fa-brands fa-js',
                'iso', 'img', 'mds', 'mdx', 'dmg', 'cdi', 'cue' => 'fa-regular fa-compact-disc',
                'vmdk', 'vhdx', 'vhd', 'vfd', 'vud', 'vdi', 'hdd', 'cow', 'qcow', 'qcow2', 'qed' => 'fa-regular fa-hard-drive',
                'db', 'frm', 'mdb', 'mdf', 'myd', 'myi', 'sqlite', 'sql' => 'fa-regular fa-database',
                'epub', 'cpz' => 'fa-brands fa-leanpub',
                'otf', 'tff', 'ttc', 'woff' => 'fa-regular fa-font',
                'ico' => 'fa-regular fa-icons',
                'lnk', 'url' => 'fa-regular fa-link',
                'ipa' => 'fa-brands fa-apple',
                'so' => 'fa-brands fa-linux',
                'rdp' => 'fa-regular fa-laptop-file',
                'omf', 'ssh', 'pub', 'ppk', 'cer', 'crt', 'der', 'p7b', 'p7c', 'p12', 'pfx', 'pem' => 'fa-regular fa-key',
                'm3u', 'm3u8' => 'fa-regular fa-rectangle-list',
                'cnf', 'conf', 'cfg' => 'fa-regular fa-gears',
                default => 'fa-regular fa-file',
            };
        }
    }
}