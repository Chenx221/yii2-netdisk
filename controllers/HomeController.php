<?php

namespace app\controllers;

use app\models\NewFolderForm;
use app\models\RenameForm;
use app\models\UploadForm;
use app\models\ZipForm;
use app\utils\FileSizeHelper;
use app\utils\FileTypeDetector;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use wapmorgan\UnifiedArchive\Exceptions\ArchiveExtractionException;
use wapmorgan\UnifiedArchive\Exceptions\FileAlreadyExistsException;
use wapmorgan\UnifiedArchive\Exceptions\UnsupportedOperationException;
use wapmorgan\UnifiedArchive\UnifiedArchive;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\filters\AccessControl;
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

    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'download', 'preview', 'rename', 'delete', 'upload', 'new-folder', 'download-folder', 'multi-ff-zip-dl', 'zip', 'unzip', 'paste'],
                            'roles' => ['user'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'download' => ['GET'],
                        'preview' => ['GET'],
                        'rename' => ['POST'],
                        'delete' => ['POST'],
                        'upload' => ['POST'], //剩余空间检查√
                        'new-folder' => ['POST'],
                        'download-folder' => ['GET'],
                        'multi-ff-zip-dl' => ['POST'],
                        'zip' => ['POST'], //剩余空间检查√
                        'unzip' => ['POST'], //剩余空间检查√
                        'paste' => ['POST'], //剩余空间检查√
                    ],
                ],
            ]
        );
    }

    /**
     * display the page of the file manager (accepts a directory parameter like cd command)
     * visit it via https://devs.chenx221.cyou:8081/index.php?r=home
     *
     * @param null $directory
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($directory = null): Response|string
    {
        $model = Yii::$app->user->identity;
//        if (Yii::$app->user->isGuest) {
//            Yii::$app->session->setFlash('error','请先登录');
//            return $this->redirect(Yii::$app->user->loginUrl);
//        } else if (!Yii::$app->user->can('accessHome')){
//            throw new NotFoundHttpException('当前用户组不允许访问此页面');
//        }
        $rootDataDirectory = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id;

        if ($directory === '.' || $directory == null) {
            $directory = null;
            $parentDirectory = null;
        } elseif (str_contains($directory, '..')) {
            throw new NotFoundHttpException('Invalid directory.');
        } else {
            $parentDirectory = dirname($directory);
        }
        $directoryContents = $this->getDirectoryContents(join(DIRECTORY_SEPARATOR, [$rootDataDirectory, $directory ?: '.']));
        foreach ($directoryContents as $key => $item) {
            $relativePath = $directory ? $directory . '/' . $item : $item;
            $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath;
            $type = FileTypeDetector::detect($absolutePath);
            $lastModified = filemtime($absolutePath);
            $size = is_file($absolutePath) ? filesize($absolutePath) : null;
            $rawType = is_file($absolutePath) ? mime_content_type($absolutePath) : null;
            $directoryContents[$key] = ['name' => $item, 'type' => $type, 'lastModified' => $lastModified, 'size' => $size, 'rawType' => $rawType];
        }
        $usedSpace = FileSizeHelper::getUserHomeDirSize();
        $vaultUsedSpace = FileSizeHelper::getUserVaultDirSize();
        $storageLimit = $model->storage_limit;
        return $this->render('index', [
            'directoryContents' => $directoryContents,
            'parentDirectory' => $parentDirectory,
            'directory' => $directory,  // 将$directory传递给视图
            'usedSpace' => $usedSpace, // B
            'vaultUsedSpace' => $vaultUsedSpace, // B
            'storageLimit' => $storageLimit, // MB
        ]);
    }

    /**
     * 获取指定路径下的文件和文件夹内容
     * @param string $path 路径
     * @return array 文件和文件夹内容数组
     * @throws NotFoundHttpException 如果路径不存在
     */
    protected function getDirectoryContents(string $path): array
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
    public function actionDownload(string $relativePath): void
    {
        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
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
     * @param string $relativePath
     * @return void
     * @throws NotFoundHttpException
     */
    public function actionPreview(string $relativePath): void
    {
        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
            throw new NotFoundHttpException('Invalid file path.');
        }

        // 确定文件的绝对路径
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        // 检查文件是否存在
        if (!file_exists($absolutePath)) {
            throw new NotFoundHttpException('File not found.');
        }

        // 获取图像的 MIME 类型
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $absolutePath);
        finfo_close($finfo);

        // 设置响应头
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($absolutePath) . '"');

        // 读取并输出图像数据
        readfile($absolutePath);

        // 结束脚本执行
        exit;
    }

    /**
     * 重命名文件或文件夹
     * @return string|Response|null
     * @throws NotFoundHttpException 如果文件或文件夹不存在
     */
    public function actionRename(): Response|string|null
    {
        $relativePath = Yii::$app->request->post('relativePath');

        // 对相对路径进行解码
        $relativePath = rawurldecode($relativePath);

        // 检查相对路径是否只包含允许的字符
        if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
            throw new NotFoundHttpException('Invalid file path.');
        }

        // 确定文件的绝对路径
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        // 检查文件或文件夹是否存在
        if (!file_exists($absolutePath)) {
            throw new NotFoundHttpException('File or directory not found.');
        }

        $model = new RenameForm();

        try {
            $model->newName = ArrayHelper::getValue(Yii::$app->request->post('RenameForm'), 'newName');
        } catch (Exception) {
            throw new NotFoundHttpException('Invalid request.');
        }

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
    public function actionDelete(): Response
    {
        $relativePaths = Yii::$app->request->post('relativePath');
        if (!is_array($relativePaths)) {
            $relativePaths = [$relativePaths];
        }

        foreach ($relativePaths as $relativePath) {
            $relativePath = rawurldecode($relativePath);
            if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
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
        }
        return $this->redirect(['index', 'directory' => dirname($relativePaths[0])]);
    }

    /**
     * 递归删除目录及其内容
     * @param string $dir 目录路径
     */
    protected function deleteDirectory(string $dir): bool
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
     * @return int|Response
     */
    public function actionUpload(): Response|int
    {
        $model = new UploadForm();
        $model->targetDir = Yii::$app->request->post('targetDir', '.');
        $uploadedFiles = UploadedFile::getInstancesByName('files');
        $successCount = 0;
        $totalCount = count($uploadedFiles);
        $sp = Yii::$app->request->post('sp', null);

        foreach ($uploadedFiles as $uploadedFile) {
            $model->uploadFile = $uploadedFile;
            if (!preg_match($this->pattern, $model->uploadFile->fullPath) || str_contains($model->uploadFile->fullPath, '..')) {
                continue;
            }
            if (!FileSizeHelper::hasEnoughSpace($model->uploadFile->size)) {
                continue;
            }
            if ($model->upload()) {
                $successCount++;
            }
        }
        if ($sp === 'editSaving') {
            if ($successCount === $totalCount) {
                Yii::$app->response->statusCode = 200;
                return $this->asJson(['status' => 200, 'message' => '文件上传成功']);
            } else {
                Yii::$app->response->statusCode = 500;
                return $this->asJson(['status' => 500, 'message' => '文件上传失败']);
            }
        } else {
            if ($successCount === $totalCount) {
                Yii::$app->session->setFlash('success', '文件上传成功');
            } elseif ($successCount > 0) {
                Yii::$app->session->setFlash('warning', '部分文件上传失败，这可能是用户剩余空间不足导致');
            } else {
                Yii::$app->session->setFlash('error', '文件上传失败，这可能是用户剩余空间不足导致');
            }
            //返回状态码200
            return Yii::$app->response->statusCode = 200;
        }
    }

    /**
     * 新建文件夹
     *
     * @return array|Response
     */
    public function actionNewFolder(): Response|array
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
    protected function getDirectorySize(string $directory): int
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
     * @throws NotFoundHttpException
     */
    public function actionDownloadFolder($relativePath): Response|\yii\console\Response
    {
        $relativePath = rawurldecode($relativePath);
        if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
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
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($absolutePath),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
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
    public function actionMultiFfZipDl(): Response|\yii\console\Response
    {
        // 获取请求中的文件和文件夹的相对路径
        $relativePaths = Yii::$app->request->post('relativePaths');

        // 对相对路径进行解码
        $relativePaths = array_map('rawurldecode', $relativePaths);

        // 初始化总大小
        $totalSize = 0;

        foreach ($relativePaths as $relativePath) {
            // 检查相对路径是否只包含允许的字符
            if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
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
                    $zip->addFile($absolutePath, $relativePath);
                } else if (is_dir($absolutePath)) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($absolutePath),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($files as $file) {
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
        return Yii::$app->response->sendFile($zipPath);
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionZip(): Response
    {
        $model = new ZipForm();
        $relativePaths = json_decode(Yii::$app->request->post('relativePath'), true);
        $targetDirectory = Yii::$app->request->post('targetDirectory') ?? '.';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (str_contains($targetDirectory, '..')) {
                throw new NotFoundHttpException('Invalid target directory.');
            } elseif (!is_dir(Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $targetDirectory)) {
                throw new NotFoundHttpException('Directory not found.');
            }
            $absolutePaths = [];
            foreach ($relativePaths as $relativePath) {
                if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
                    continue;
                }
                $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;
                if (!file_exists($absolutePath)) {
                    throw new NotFoundHttpException('The requested file does not exist.');
                }
                $absolutePaths[] = $absolutePath;
            }
            $zipPath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $targetDirectory . '/' . $model->zipFilename . '.' . $model->zipFormat;
            try {
                UnifiedArchive::create($absolutePaths, $zipPath);
                // 获取新的压缩文件的大小
                $zipSize = filesize($zipPath);
                // 检查新的压缩文件的大小是否超过用户的存储限制
                if (!FileSizeHelper::hasEnoughSpace()) {
                    // 如果超过，删除这个新的压缩文件，并显示一个错误消息
                    unlink($zipPath);
                    Yii::$app->session->setFlash('error', '打包失败,空间不足');
                } else {
                    Yii::$app->session->setFlash('success', '打包成功');
                }
            } catch (FileAlreadyExistsException) {
                Yii::$app->session->setFlash('error', '目标文件夹已存在同名压缩档案');
            } catch (UnsupportedOperationException) {
                Yii::$app->session->setFlash('error', '不支持的操作');
            }
        }
        return $this->redirect(['index', 'directory' => $targetDirectory]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUnzip(): array
    {
        $relativePath = Yii::$app->request->post('relativePath');
        if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
            throw new NotFoundHttpException('Invalid file path.');
        }
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;

        if (!file_exists($absolutePath)) {
            throw new NotFoundHttpException('File not found.');
        }
        $archive = UnifiedArchive::open($absolutePath);
        if ($archive === null) {
            throw new NotFoundHttpException('Failed to open the archive.');
        }
        $now_time = time();
        $targetDirectory = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . pathinfo($relativePath, PATHINFO_FILENAME) . '_' . $now_time;
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        try {
            $archive->extract($targetDirectory);
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (!FileSizeHelper::hasEnoughSpace()) {
                $this->deleteDirectory($targetDirectory);
                Yii::$app->session->setFlash('error', '解压失败,空间不足');
                return [
                    'status' => 500,
                    'directory' => pathinfo($relativePath, PATHINFO_FILENAME) . '_' . $now_time,
                    'parentDirectory' => dirname($relativePath),
                ];
            }else{
                Yii::$app->session->setFlash('success', '解压成功');
                return [
                    'status' => 200,
                    'directory' => pathinfo($relativePath, PATHINFO_FILENAME) . '_' . $now_time,
                ];
            }
        } catch (ArchiveExtractionException) {
            $this->deleteDirectory($targetDirectory);
            Yii::$app->session->setFlash('error', '解压过程中出现错误');
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 500,
                'directory' => pathinfo($relativePath, PATHINFO_FILENAME) . '_' . $now_time,
                'parentDirectory' => dirname($relativePath),
            ];
        }
    }

    /**
     *
     *
     * @return Response
     */
    public function actionPaste(): Response
    {
        // 获取请求中的操作类型、相对路径和目标目录
        $operation = Yii::$app->request->post('operation');
        $relativePaths = Yii::$app->request->post('relativePaths');
        $targetDirectory = Yii::$app->request->post('targetDirectory');

        // 对相对路径进行解码
        $relativePaths = array_map('rawurldecode', $relativePaths);

        // 对每个相对路径进行处理
        foreach ($relativePaths as $relativePath) {
            // 确定源文件/目录和目标文件/目录的绝对路径
            $sourcePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;
            $targetPath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $targetDirectory . '/' . basename($relativePath);

            // 检查目标路径是否是源路径的子路径 // 你是白痴吗
            if ($operation === 'cut' && is_dir($sourcePath) && str_starts_with($targetPath, $sourcePath)) {
                Yii::$app->session->setFlash('error', 'Cannot move a directory into itself.');
                return $this->redirect(['index', 'directory' => $targetDirectory]);
            }

            // 检查目标路径是否已经存在同名文件或目录
            if ($operation === 'copy' && file_exists($targetPath)) {
                $sourceFileSize = filesize($sourcePath);

                // 检查是否有足够的空间进行复制操作
                if (!FileSizeHelper::hasEnoughSpace($sourceFileSize)) {
                    Yii::$app->session->setFlash('error', 'Not enough space to copy the file.');
                    return $this->redirect(['index', 'directory' => $targetDirectory]);
                }
                $pathInfo = pathinfo($targetPath);
                $i = 1;
                do {
                    $newFilename = $pathInfo['filename'] . '_副本' . ($i > 1 ? $i : '');
                    $newTargetPath = $pathInfo['dirname'] . '/' . $newFilename . '.' . $pathInfo['extension'];
                    $i++;
                } while (file_exists($newTargetPath));
                $targetPath = $newTargetPath;
            }

            // 根据操作类型执行相应的操作
            if ($operation === 'copy') {
                $sourceSize = is_dir($sourcePath) ? FileSizeHelper::getDirectorySize($sourcePath) : filesize($sourcePath);
                if (!FileSizeHelper::hasEnoughSpace($sourceSize)) {
                    Yii::$app->session->setFlash('error', 'Not enough space to copy.');
                    return $this->redirect(['index', 'directory' => $targetDirectory]);
                }
                if (is_dir($sourcePath)) {
                    // 如果源路径是目录，递归复制目录
                    if (!$this->copyDirectory($sourcePath, $targetPath)) {
                        Yii::$app->session->setFlash('error', 'Failed to copy directory.');
                        return $this->redirect(['index', 'directory' => $targetDirectory]);
                    }
                } else {
                    // 如果源路径是文件，使用PHP的copy函数复制文件
                    if (!copy($sourcePath, $targetPath)) {
                        Yii::$app->session->setFlash('error', 'Failed to copy file.');
                        return $this->redirect(['index', 'directory' => $targetDirectory]);
                    }
                }
            } elseif ($operation === 'cut') {
                // 如果是剪切操作
                if (is_dir($sourcePath)) {
                    // 如果源路径是目录，递归移动目录
                    if (!$this->moveDirectory($sourcePath, $targetPath)) {
                        Yii::$app->session->setFlash('error', 'Failed to move directory.');
                        return $this->redirect(['index', 'directory' => $targetDirectory]);
                    }
                } else {
                    // 如果源路径是文件，使用PHP的rename函数移动文件
                    if (!rename($sourcePath, $targetPath)) {
                        Yii::$app->session->setFlash('error', 'Failed to move file.');
                        return $this->redirect(['index', 'directory' => $targetDirectory]);
                    }
                }
            }
        }

        Yii::$app->session->setFlash('success', 'Operation completed successfully.');
        return $this->redirect(['index', 'directory' => $targetDirectory]);
    }

    /**
     * 递归复制目录及其内容
     * @param string $source 源目录路径
     * @param string $destination 目标目录路径
     * @return bool 操作是否成功
     */
    protected function copyDirectory(string $source, string $destination): bool
    {
        // 创建目标目录
        if (!mkdir($destination)) {
            return false;
        }

        // 打开源目录
        $dir = opendir($source);
        if ($dir === false) {
            return false;
        }

        // 递归复制目录的内容
        while (($entry = readdir($dir)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $sourcePath = $source . '/' . $entry;
            $destinationPath = $destination . '/' . $entry;

            if (is_dir($sourcePath)) {
                // 如果源路径是目录，递归复制目录
                if (!$this->copyDirectory($sourcePath, $destinationPath)) {
                    return false;
                }
            } else {
                // 如果源路径是文件，复制文件
                if (!copy($sourcePath, $destinationPath)) {
                    return false;
                }
            }
        }

        closedir($dir);
        return true;
    }

    /**
     * 递归移动目录及其内容
     * @param string $source 源目录路径
     * @param string $destination 目标目录路径
     * @return bool 操作是否成功
     */
    protected function moveDirectory(string $source, string $destination): bool
    {
        // 创建目标目录
        if (!mkdir($destination)) {
            return false;
        }

        // 打开源目录
        $dir = opendir($source);
        if ($dir === false) {
            return false;
        }

        // 递归移动目录的内容
        while (($entry = readdir($dir)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $sourcePath = $source . '/' . $entry;
            $destinationPath = $destination . '/' . $entry;

            if (is_dir($sourcePath)) {
                // 如果源路径是目录，递归移动目录
                if (!$this->moveDirectory($sourcePath, $destinationPath)) {
                    return false;
                }
            } else {
                // 如果源路径是文件，移动文件
                if (!rename($sourcePath, $destinationPath)) {
                    return false;
                }
            }
        }

        closedir($dir);

        // 删除源目录
        if (!rmdir($source)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionChecksum(): array
    {
        $relativePath = Yii::$app->request->post('relativePath');
        if (!preg_match($this->pattern, $relativePath) || str_contains($relativePath, '..')) {
            throw new NotFoundHttpException('Invalid file path.');
        }
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '/' . $relativePath;
        if (!is_file($absolutePath)) {
            throw new NotFoundHttpException('The specified path does not point to a file.');
        }
        // 计算文件的校验值
        $crc32b = hash_file('crc32b', $absolutePath);
        $sha256 = hash_file('sha256', $absolutePath);

        // 将校验值返回给客户端
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'crc32b' => strtoupper($crc32b),
            'sha256' => strtoupper($sha256),
        ];
    }
}
