<?php
namespace cliff363825\kindeditor;

use Yii;
use yii\base\Action;

/**
 * Class KindEditorFileManagerAction
 * @package cliff363825\kindeditor
 * @property string $rootPath
 * @property string $rootUrl
 */
class KindEditorFileManagerAction extends Action
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
     * the sort definition for this file manager.
     * 排序
     * @var string
     */
    public $order = 'name';
    /**
     * the root directory of the file manager.
     * 根目录路径，可以指定绝对路径，比如 /var/www/attached/
     * @var string
     */
    private $_rootPath = '@webroot/uploads';
    /**
     * 根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
     * @var string
     */
    private $_rootUrl = '@web/uploads';

    /**
     * Runs the action
     */
    public function run()
    {
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = $this->getRootPath() . '/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = $this->getRootUrl() . '/';
        if (!file_exists($root_path)) {
            mkdir($root_path, 0777, true);
        }
        //图片扩展名
        $ext_arr = !empty($this->extensions['image']) ? $this->extensions['image'] : [];

        //目录名
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if ($dir_name !== '' && !isset($this->extensions[$dir_name])) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                mkdir($root_path);
            }
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
        $this->order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
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

        usort($file_list, [$this, 'cmp_func']);

        $result = [];
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
        exit;
    }

    /**
     * 排序
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function cmp_func($a, $b)
    {
        $order = $this->order;
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return rtrim(Yii::getAlias($this->_rootPath), '\\/');
    }

    /**
     * @param string $rootPath
     */
    public function setRootPath($rootPath)
    {
        $this->_rootPath = $rootPath;
    }

    /**
     * @return string
     */
    public function getRootUrl()
    {
        return rtrim(Yii::getAlias($this->_rootUrl), '\\/');
    }

    /**
     * @param string $rootUrl
     */
    public function setRootUrl($rootUrl)
    {
        $this->_rootUrl = $rootUrl;
    }
}