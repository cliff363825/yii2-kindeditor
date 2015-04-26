<?php

namespace cliff363825\kindeditor;

use Yii;
use yii\web\Controller;

class KindEditorController extends Controller
{
    /**
     * csrf cookie param
     * @var string
     */
    public $csrfCookieParam = '_csrfCookie';

    /**
     * file upload max size
     * default: 2MB
     * @var integer
     */
    private $_maxSize = 2097152;

    /**
     * file sort
     * optional: name, size, type
     * @var string
     */
    private $_order = 'name';

    /**
     * @var string
     */
    private $_uploadPath = '@webroot/uploads';

    /**
     * @var string
     */
    private $_uploadUrl = '@web/uploads';

    /**
     * @var string
     */
    private $_subDir;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (($sessionId = Yii::$app->request->post(Yii::$app->session->name)) !== null) {
            Yii::$app->session->setId($sessionId);
            Yii::$app->session->open();
        }
        if (Yii::$app->request->enableCsrfCookie) {
            $csrfParam = Yii::$app->request->csrfParam;
            // fix bug #1: 400 bad request [by fdddf]
            if (!isset($_COOKIE[$csrfParam])) {
                $_COOKIE[$csrfParam] = Yii::$app->request->post($this->csrfCookieParam);
            }
        }
    }

    /**
     * File Manage Action
     * @throws \yii\base\ExitException
     */
    public function actionFilemanager()
    {
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = $this->getUploadPath();
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = $this->getUploadUrl();
        //图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        //目录名
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            Yii::$app->end();
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
        }
        if (!is_dir($root_path)) {
            mkdir($root_path, 0775, true);
        }
        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        //echo realpath($root_path);
        //排序形式，name or size or type
        if (!empty($_GET['order'])) {
            $this->setOrder(strtolower($_GET['order']));
        }
        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            Yii::$app->end();
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            Yii::$app->end();
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            Yii::$app->end();
        }
        //遍历目录取得文件信息
        $file_list = array();
        if (($handle = opendir($current_path)) !== false) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') {
                    continue;
                }
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        //排序
        usort($file_list, array($this, 'cmp_func'));
        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;
        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($result);
        Yii::$app->end();
    }

    /**
     * File Upload Action
     * @throws \yii\base\ExitException
     */
    public function actionUpload()
    {
        //文件保存目录路径
        $save_path = $this->getUploadPath();
        //文件保存目录URL
        $save_url = $this->getUploadUrl();
        //定义允许上传的文件扩展名
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );
        //最大文件大小
        $max_size = $this->getMaxSize();
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
            if (is_dir($save_path) === false) {
                $this->alert("上传目录不存在。");
            }
            //检查目录写权限
            if (is_writable($save_path) === false) {
                $this->alert("上传目录没有写权限。");
            }
            //检查是否已上传
            if (is_uploaded_file($tmp_name) === false) {
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
            $ymd = $this->getSubDir();
            if (!empty($ymd)) {
                $save_path .= $ymd . "/";
                $save_url .= $ymd . "/";
            }
            if (!is_dir($save_path)) {
                mkdir($save_path, 0775, true);
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
            echo json_encode(array('error' => 0, 'url' => $file_url));
            Yii::$app->end();
        }
    }

    public function setMaxSize($maxSize)
    {
        $this->_maxSize = (int)$maxSize;
    }

    public function getMaxSize()
    {
        if ($this->_maxSize === null) {
            $this->_maxSize = 2097152; // 2M
        }
        return $this->_maxSize;
    }

    public function setOrder($order)
    {
        if (!in_array($order, array('name', 'size', 'type'))) {
            $order = 'name';
        }
        $this->_order = $order;
    }

    public function getOrder()
    {
        if ($this->_order === null || !in_array($this->_order, array('name', 'size', 'type'))) {
            $this->_order = 'name';
        }
        return $this->_order;
    }

    public function setUploadPath($uploadPath)
    {
        $this->_uploadPath = $uploadPath;
    }

    public function getUploadPath()
    {
        if ($this->_uploadPath === null) {
            $this->_uploadPath = '@webroot/uploads';
        }
        return rtrim(Yii::getAlias($this->_uploadPath), "\\/") . '/';
    }

    public function setUploadUrl($uploadUrl)
    {
        $this->_uploadUrl = $uploadUrl;
    }

    public function getUploadUrl()
    {
        if ($this->_uploadUrl === null) {
            $this->_uploadUrl = '@web/uploads';
        }
        return rtrim(Yii::getAlias($this->_uploadUrl), "\\/") . '/';
    }

    public function setSubDir($subDir)
    {
        $this->_subDir = $subDir;
    }

    public function getSubDir()
    {
        if ($this->_subDir === null) {
            $this->_subDir = date('Ym/d');
        }
        return rtrim($this->_subDir, "\\/");
    }

    protected function cmp_func($a, $b)
    {
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($this->getOrder() == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($this->getOrder() == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }

    protected function alert($msg)
    {
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(array('error' => 1, 'message' => $msg));
        Yii::$app->end();
    }
}
