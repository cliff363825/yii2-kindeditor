<?php
namespace cliff363825\kindeditor;

use Yii;
use yii\base\Action;

/**
 * Class KindEditorUploadAction
 * @package cliff363825\kindeditor
 * @property string $savePath
 * @property string $saveUrl
 * @property int $maxSize
 */
class KindEditorUploadAction extends Action
{
    /**
     * a list of file name extensions that are allowed to be uploaded.
     * 定义允许上传的文件扩展名
     * @var array
     */
    public $extensions = [
        'image' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
        'flash' => ['swf', 'flv'],
        'media' => ['swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'],
        'file' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'],
    ];
    /**
     * the file path used to save the uploaded file
     * 文件保存目录路径
     * @var string
     */
    private $_savePath = '@webroot/uploads';
    /**
     * 文件保存目录URL
     * @var string
     */
    private $_saveUrl = '@web/uploads';
    /**
     * the maximum number of bytes required for the uploaded file.
     * 最大文件大小
     * @var int
     */
    private $_maxSize = 1000000;

    /**
     * Runs the action
     */
    public function run()
    {
        //文件保存目录路径
        $save_path = $this->getSavePath() . '/';
        //文件保存目录URL
        $save_url = $this->getSaveUrl() . '/';
        //定义允许上传的文件扩展名
        $ext_arr = $this->extensions;;
        //最大文件大小
        $max_size = $this->getMaxSize();

        if (!file_exists($save_path)) {
            mkdir($save_path, 0777, true);
        }
        $save_path = realpath($save_path) . '/';

        //PHP上传失败
        if (!empty($_FILES['imgFile']['error'])) {
            switch ($_FILES['imgFile']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }

        //有上传文件时
        if (empty($_FILES) === false) {
            //原文件名
            $file_name = $_FILES['imgFile']['name'];
            //服务器上临时文件名
            $tmp_name = $_FILES['imgFile']['tmp_name'];
            //文件大小
            $file_size = $_FILES['imgFile']['size'];
            //检查文件名
            if (!$file_name) {
                $this->alert("请选择文件。");
            }
            //检查目录
            if (@is_dir($save_path) === false) {
                $this->alert("上传目录不存在。");
            }
            //检查目录写权限
            if (@is_writable($save_path) === false) {
                $this->alert("上传目录没有写权限。");
            }
            //检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->alert("上传失败。");
            }
            //检查文件大小
            if ($file_size > $max_size) {
                $this->alert("上传文件大小超过限制。");
            }
            //检查目录名
            $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
            if (empty($ext_arr[$dir_name])) {
                $this->alert("目录名不正确。");
            }
            //获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            //检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
            }
            //创建文件夹
            if ($dir_name !== '') {
                $save_path .= $dir_name . "/";
                $save_url .= $dir_name . "/";
            }
            $ymd = date("Ym/d");
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path, 0777, true);
            }
            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            //移动文件
            $file_path = $save_path . $new_file_name;
            if (move_uploaded_file($tmp_name, $file_path) === false) {
                $this->alert("上传文件失败。");
            }
            @chmod($file_path, 0644);
            $file_url = $save_url . $new_file_name;

            header('Content-type: text/html; charset=UTF-8');
            echo json_encode(['error' => 0, 'url' => $file_url]);
            exit;
        }
    }

    /**
     * @param string $msg
     */
    protected function alert($msg)
    {
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(['error' => 1, 'message' => $msg]);
        exit;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return rtrim(Yii::getAlias($this->_savePath), '\\/');
    }

    /**
     * @param string $savePath
     */
    public function setSavePath($savePath)
    {
        $this->_savePath = $savePath;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return rtrim(Yii::getAlias($this->_saveUrl), '\\/');
    }

    /**
     * @param string $saveUrl
     */
    public function setSaveUrl($saveUrl)
    {
        $this->_saveUrl = $saveUrl;
    }

    /**
     * @return int
     */
    public function getMaxSize()
    {
        return $this->_maxSize;
    }

    /**
     * @param int $maxSize
     */
    public function setMaxSize($maxSize)
    {
        $this->_maxSize = (int)$maxSize;
    }
}