<?php

namespace app\controllers;

use app\utils\FileTypeDetector;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class HomeController extends \yii\web\Controller
{
    /**
     * diplay the page of the file manager (accepts a directory parameter like cd command)
     * visit it via https://devs.chenx221.cyou:8081/index.php?r=home
     *
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($directory = null)
    {
        //Warning: Security Vulnerability: access via $directory parameter = ../ will display the internal files of the server
        if (Yii::$app->user->isGuest) {
            return $this->redirect(Yii::$app->user->loginUrl);
        }
        $rootDataDirectory = Yii::getAlias(Yii::$app->params['dataDirectory']);
        $userId = Yii::$app->user->id;

        if ($directory == null | $directory == '.') {
            $parentDirectory = null;
        } else {
            $parentDirectory = dirname($directory);
        }
        $directoryContents = $this->getDirectoryContents(join(DIRECTORY_SEPARATOR, [$rootDataDirectory, $userId, $directory ?: '.']));
        foreach ($directoryContents as $key => $item) {
            $relativePath = $directory ? $directory . '/' . $item : $item;
            $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath;
            $type = FileTypeDetector::detect($absolutePath);
            $directoryContents[$key] = ['name' => $item, 'type' => $type];
        }
        return $this->render('index', [
            'directoryContents' => $directoryContents,
            'parentDirectory' => $parentDirectory,
            'directory' => $directory,  // 将$directory传递给视图
        ]);
    }

    /**
     * 获取指定路径下的文件和文件夹内容
     * @param string $path 路径
     * @return array 文件和文件夹内容数组
     * @throws NotFoundHttpException 如果路径不存在
     */
    protected function getDirectoryContents($path)
    {
        // 确定路径是否存在
        if (!is_dir($path)) {
            throw new NotFoundHttpException('Directory not found.');
        }

        // 获取路径下的所有文件和文件夹
        $directoryContents = scandir($path);

        return array_diff($directoryContents, ['.', '..']);
    }

    /**
     * 下载指定路径下的文件
     * must be provided with a relative path of the file
     * download link:https://devs.chenx221.cyou:8081/index.php?r=home%2Fdownload&relativePath={the relative path of the file}
     *
     * @param string $relativePath 文件的相对路径
     * @throws NotFoundHttpException 如果文件不存在
     */
    public function actionDownload($relativePath)
    {
        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match('/^[\w\-.\/]+$/u', $relativePath)) {
            throw new NotFoundHttpException('Invalid file path.');
        }

        // 确定文件的绝对路径
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        // 使用realpath函数解析路径，并检查解析后的路径是否在预期的目录中
        $realPath = realpath($absolutePath);
        $dataDirectory = str_replace('/', '\\', Yii::getAlias(Yii::$app->params['dataDirectory']));
        if (!$realPath || !str_starts_with($realPath, $dataDirectory)) {
            throw new NotFoundHttpException('File not found.');
        }

        // 检查文件是否存在
        if (!file_exists($realPath)) {
            throw new NotFoundHttpException('File not found.');
        }

        // 将文件发送给用户进行下载
        Yii::$app->response->sendFile($realPath)->send();
    }
}
