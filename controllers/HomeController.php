<?php

namespace app\controllers;

use app\models\NewFolderForm;
use app\models\RenameForm;
use app\models\UploadForm;
use app\utils\FileTypeDetector;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use ZipArchive;

class HomeController extends Controller
{
    protected string $pattern = '/^[^\p{C}:*?"<>|\\\\]+$/u';

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
                        'upload' => ['POST'],
                        'new-folder' => ['POST'],
                        'download-folder' => ['GET'],
                        'multi-ff-zip-dl' => ['POST'],
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

        if ($directory === '.' || $directory == null) {
            $directory = null;
            $parentDirectory = null;
        } elseif ($directory === '..' || str_contains($directory, '../')) {
            throw new NotFoundHttpException('Invalid directory.');
        } else {
            $parentDirectory = dirname($directory);
        }
        $directoryContents = $this->getDirectoryContents(join(DIRECTORY_SEPARATOR, [$rootDataDirectory, $userId, $directory ?: '.']));
        foreach ($directoryContents as $key => $item) {
            $relativePath = $directory ? $directory . '/' . $item : $item;
            $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath;
            $type = FileTypeDetector::detect($absolutePath);
            $lastModified = filemtime($absolutePath);
            $size = is_file($absolutePath) ? filesize($absolutePath) : null;
            $directoryContents[$key] = ['name' => $item, 'type' => $type, 'lastModified' => $lastModified, 'size' => $size];
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

        // 移除 '.' 和 '..'
        $directoryContents = array_diff($directoryContents, ['.', '..']);

        // 使用 usort 对目录内容进行排序，使文件夹始终在文件之前
        usort($directoryContents, function ($a, $b) use ($path) {
            $aIsDir = is_dir($path . '/' . $a);
            $bIsDir = is_dir($path . '/' . $b);
            if ($aIsDir === $bIsDir) {
                return strnatcasecmp($a, $b); // 如果两者都是文件夹或都是文件，按名称排序
            }
            return $aIsDir ? -1 : 1; // 文件夹始终在文件之前
        });

        return $directoryContents;
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
        if (!preg_match($this->pattern, $relativePath) || $relativePath === '.' || $relativePath === '..' || str_contains($relativePath, '../')) {
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
     * @return string|Response|null
     * @throws NotFoundHttpException 如果文件或文件夹不存在
     */
    public function actionRename()
    {
        $relativePath = Yii::$app->request->post('relativePath');

        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match($this->pattern, $relativePath) || $relativePath === '.' || $relativePath === '..' || str_contains($relativePath, '../')) {
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
        $relativePath = Yii::$app->request->post('relativePath');
        $relativePath = rawurldecode($relativePath);
        if (!preg_match($this->pattern, $relativePath) || $relativePath === '.' || $relativePath === '..' || str_contains($relativePath, '../')) {
            throw new NotFoundHttpException('Invalid file path.');
        }
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;
        if (!file_exists($absolutePath)) {
            throw new NotFoundHttpException('File or directory not found.');
        } else {
            $realPath = realpath($absolutePath);
            $expectedPathPrefix = realpath(Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id);
            if (!str_starts_with($realPath, $expectedPathPrefix)) {
                throw new NotFoundHttpException('File or directory not found.');
            }
        }

        if (is_dir($absolutePath)) {
            if (!$this->deleteDirectory($absolutePath)) {
                Yii::$app->session->setFlash('error', 'Failed to delete directory.');
            } else {
                Yii::$app->session->setFlash('success', 'Directory deleted successfully.');
            }
        } else {
            if (!unlink($absolutePath)) {
                Yii::$app->session->setFlash('error', 'Failed to delete file.');
            } else {
                Yii::$app->session->setFlash('success', 'File deleted successfully.');
            }
        }
        return $this->redirect(['index', 'directory' => dirname($relativePath)]);
    }

    /**
     * 递归删除目录及其内容
     * @param string $dir 目录路径
     */
    protected function deleteDirectory($dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                if (!$this->deleteDirectory("$dir/$file")) {
                    return false;
                }
            } else {
                if (!unlink("$dir/$file")) {
                    return false;
                }
            }
        }
        if (!rmdir($dir)) {
            return false;
        }
        return true;
    }

    /**
     * 文件、文件夹上传
     * 注意,已存在的同名文件会被覆盖
     * https://devs.chenx221.cyou:8081/index.php?r=home%2Fupload
     *
     * @return string|Response
     */
    public function actionUpload()
    {
        $model = new UploadForm();
        $model->targetDir = Yii::$app->request->post('targetDir', '.');
        $uploadedFiles = UploadedFile::getInstancesByName('files');
        $successCount = 0;
        $totalCount = count($uploadedFiles);

        foreach ($uploadedFiles as $uploadedFile) {
            $model->uploadFile = $uploadedFile;
            if (!preg_match($this->pattern, $model->uploadFile->fullPath) || $model->uploadFile->fullPath === '.' || $model->uploadFile->fullPath === '..' || str_contains($model->uploadFile->fullPath, '../')) {
                continue;
            }
            if ($model->upload()) {
                $successCount++;
            }
        }

        if ($successCount === $totalCount) {
            Yii::$app->session->setFlash('success', 'All files uploaded successfully.');
        } elseif ($successCount > 0) {
            Yii::$app->session->setFlash('warning', 'Some files uploaded successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to upload files.');
        }
        //返回状态码200
        return Yii::$app->response->statusCode = 200; // 如果出错请删掉return
    }

    /**
     * 新建文件夹
     *
     * @return array|string|Response
     */
    public function actionNewFolder()
    {

        $relativePath = Yii::$app->request->post('relativePath');
        $relativePath = rawurldecode($relativePath);
        $model = new NewFolderForm();

        if ($model->load(Yii::$app->request->post())) {
            $model->relativePath = $relativePath;
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            if ($model->validate()) {
                if ($model->createFolder()) {
                    Yii::$app->session->setFlash('success', 'Folder created successfully.');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to create folder.');
                }
            } else {
                $errors = $model->errors;
                foreach ($errors as $error) {
                    Yii::$app->session->setFlash('error', $error[0]);
                }
            }
        }
        return $this->redirect(['index', 'directory' => $relativePath]);

    }

    /**
     * 计算指定目录的大小
     *
     * @param string $directory 目录路径
     * @return int 目录的大小（字节）
     */
    protected function getDirectorySize($directory)
    {
        $size = 0;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDownloadFolder($relativePath)
    {
        $relativePath = rawurldecode($relativePath);
        if (!preg_match($this->pattern, $relativePath) || $relativePath === '.' || $relativePath === '..' || str_contains($relativePath, '../')) {
            throw new NotFoundHttpException('Invalid path.');
        }
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        if (!is_dir($absolutePath)) {
            throw new NotFoundHttpException('Directory not found.');
        }
        $size = $this->getDirectorySize($absolutePath);

        // 检查需要下载的文件夹大小是否超过200MB
        if ($size > 200 * 1024 * 1024) {
            throw new NotFoundHttpException('Directory size exceeds the limit of 200MB.');
        } else {
            $zip = new ZipArchive();
            $tempDir = sys_get_temp_dir() . '/';
            $zipPath = $tempDir . basename($absolutePath) . '.zip';

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($absolutePath),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($absolutePath) + 1);

                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();
            }
            return Yii::$app->response->sendFile($zipPath);
        }
    }

    /**
     * @return \yii\console\Response|Response
     * @throws NotFoundHttpException
     */
    public function actionMultiFfZipDl()
    {
        // 获取请求中的文件和文件夹的相对路径
        $relativePaths = Yii::$app->request->post('relativePaths');

        // 对相对路径进行解码
        $relativePaths = array_map('rawurldecode', $relativePaths);

        // 初始化总大小
        $totalSize = 0;

        foreach ($relativePaths as $relativePath) {
            // 检查相对路径是否只包含允许的字符
            if (!preg_match($this->pattern, $relativePath) || $relativePath === '.' || $relativePath === '..' || str_contains($relativePath, '../')) {
                throw new NotFoundHttpException('Invalid file path.');
            }

            // 确定文件或文件夹的绝对路径
            $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

            // 检查文件或文件夹是否存在
            if (!file_exists($absolutePath)) {
                throw new NotFoundHttpException('File or directory not found.');
            }

            // 计算文件或文件夹的大小，并添加到总大小
            if (is_dir($absolutePath)) {
                $totalSize += $this->getDirectorySize($absolutePath);
            } else {
                $totalSize += filesize($absolutePath);
            }
        }

        // 检查总大小是否超过200MB
        if ($totalSize > 200 * 1024 * 1024) {
            throw new NotFoundHttpException('Total size exceeds the limit of 200MB.');
        }

        // 创建一个新的ZipArchive实例
        $zip = new ZipArchive();
        $tempDir = sys_get_temp_dir() . '/';
        $zipPath = $tempDir . time() . '.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach ($relativePaths as $relativePath) {
                $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

                if (is_file($absolutePath)) {
                    // 如果是文件，直接添加到 zip 文件
                    $zip->addFile($absolutePath, $relativePath);
                } else if (is_dir($absolutePath)) {
                    // 如果是目录，获取目录中的所有文件并添加到 zip 文件
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($absolutePath),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $name => $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePathInZip = $relativePath . '/' . substr($filePath, strlen($absolutePath) + 1);

                            $zip->addFile($filePath, $relativePathInZip);
                        }
                    }
                }
            }

            $zip->close();
        }

        // 发送下载
        return Yii::$app->response->sendFile($zipPath);
    }
}
