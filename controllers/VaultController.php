<?php
/*
 * 保险箱设计: 仅文件上传下载及删除，不支持文件夹
 * 有些文件管理的代码迁移过来没删干净，这个忽略一下
 */

namespace app\controllers;

use app\models\UploadForm;
use app\utils\FileSizeHelper;
use app\utils\FileTypeDetector;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class VaultController extends Controller
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
                            'actions' => ['index', 'download', 'delete', 'upload'],
                            'roles' => ['user'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'download' => ['GET'],
                        'delete' => ['POST'],
                        'upload' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * @param null $directory
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($directory = null): Response|string
    {
        $model = Yii::$app->user->identity;

        $rootDataDirectory = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '.secret';

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
            $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '.secret/' . $relativePath;
            $type = FileTypeDetector::detect($absolutePath);
            $lastModified = filemtime($absolutePath);
            $size = is_file($absolutePath) ? filesize($absolutePath) : null;
            $rawType = is_file($absolutePath) ? mime_content_type($absolutePath) : null;
            $directoryContents[$key] = ['name' => $item, 'type' => $type, 'lastModified' => $lastModified, 'size' => $size, 'rawType' => $rawType];
        }
        $usedSpace = FileSizeHelper::getUserHomeDirSize();
        $vaultUsedSpace = FileSizeHelper::getUserVaultDirSize();  // 保险箱已用空间，暂时为0
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
        $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '.secret/' . $relativePath;

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
     * 删除文件
     * @throws NotFoundHttpException 如果文件不存在
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
            $absolutePath = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '.secret/' . $relativePath;
            if (!file_exists($absolutePath)) {
                throw new NotFoundHttpException('File or directory not found.');
            } else {
                $realPath = realpath($absolutePath);
                $expectedPathPrefix = realpath(Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id . '.secret');
                if (!str_starts_with($realPath, $expectedPathPrefix)) {
                    throw new NotFoundHttpException('File or directory not found.');
                }
            }
            if (!unlink($absolutePath)) {
                Yii::$app->session->setFlash('error', 'Failed to delete file.');
            } else {
                Yii::$app->session->setFlash('success', 'File deleted successfully.');
            }
        }
        return $this->redirect(['index', 'directory' => dirname($relativePaths[0])]);
    }

    /**
     * 文件上传
     * 注意,已存在的同名文件会被覆盖
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
            if ($model->upload(1)) {
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
}