<?php

namespace app\controllers;

use app\models\RenameForm;
use app\utils\FileTypeDetector;
use InvalidArgumentException;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class HomeController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'download' => ['GET'],
                        'rename' => ['POST'],
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

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

    /**
     * 重命名文件或文件夹
     * @param string $relativePath 文件或文件夹的相对路径
     * @param string $newName 新名称
     * @throws NotFoundHttpException 如果文件或文件夹不存在
     */
    public function actionRename()
    {
        $relativePath = Yii::$app->request->post('relativePath');

        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match('/^[\w\-.\/]+$/u', $relativePath)) {
            throw new NotFoundHttpException('Invalid file path.');
        }

        // 确定文件的绝对路径
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        // 检查文件或文件夹是否存在
        if (!file_exists($absolutePath)) {
            throw new NotFoundHttpException('File or directory not found.');
        }

        $model = new RenameForm();

        $model->newName = ArrayHelper::getValue(Yii::$app->request->post('RenameForm'), 'newName');

        if (!$model->validate()) {
            Yii::$app->response->statusCode = 400;
            return $model->getFirstError('newName');
        }

        // 检查新名称是否和现有的文件或文件夹重名
        if (file_exists(dirname($absolutePath) . '/' . $model->newName)) {
            Yii::$app->session->setFlash('error', 'Failed to rename file or directory.');
            return $this->redirect(['index', 'directory' => dirname($relativePath)]);
        }

        // 重命名文件或文件夹
        if (!rename($absolutePath, dirname($absolutePath) . '/' . $model->newName)) {
            Yii::$app->session->setFlash('error', 'Failed to rename file or directory.');
        } else {
            Yii::$app->session->setFlash('success', 'File or directory renamed successfully.');
        }
        return $this->redirect(['index', 'directory' => dirname($relativePath)]);
    }

    /**
     * 删除文件或文件夹
     * @throws NotFoundHttpException 如果文件或文件夹不存在
     */
    public function actionDelete()
    {
        // 从 POST 请求中获取 relativePath 参数
        $relativePath = Yii::$app->request->post('relativePath');

        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match('/^[\w\-.\/]+$/u', $relativePath)) {
            throw new NotFoundHttpException('Invalid file path.');
        }

        // 确定文件的绝对路径
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        // 检查文件或文件夹是否存在
        if (!file_exists($absolutePath)) {
            throw new NotFoundHttpException('File or directory not found.');
        }

        // 删除文件或文件夹
        if (is_dir($absolutePath)) {
            // 如果是文件夹，递归删除文件夹及其内容
            $this->deleteDirectory($absolutePath);
        } else {
            // 如果是文件，直接删除文件
            $fileinfo = $absolutePath; //要删了，准备一下
            $fileinfo2 = $fileinfo;
            //unlink($absolutePath);
        }
    }

    /**
     * 递归删除目录及其内容
     * @param string $dir 目录路径
     */
    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $dir1 = $dir;
            $file1 = $file;
            $file2 = $file1;
            is_dir("$dir/$file") ? $this->deleteDirectory("$dir/$file") : ''; //unlink("$dir/$file");
        }
        $dir1 = $dir;
        $dir2 = $dir1;
        rmdir($dir);
    }
}
