<?php
$url = urldecode( $_GET['magnet'] );

$torrent = getUrl($url, '', 'test');

echo "<pre>";
var_dump($torrent);
die();

require('lightbenc.php');
$info = Lightbenc::bdecode_getinfo($torrent);

echo "<pre>";
var_dump($info);
die();


curl_close($ch);



if ($magnet = generateMagnet($info)) {
    success($magnet);
} else {
    failed();
}

function getUrl($url, $save_dir = '', $filename = '', $type = 0)
{
    if (trim($url) == '') {
        return array('file_name' => '', 'save_path' => '', 'error' => 1);
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (trim($filename) == '') {//保存文件名
        $ext = strrchr($url, '.');
        if ($ext != '.gif' && $ext != '.jpg') {
            return array('file_name' => '', 'save_path' => '', 'error' => 3);
        }
        $filename = time() . $ext;
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir .= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return array('file_name' => '', 'save_path' => '', 'error' => 5);
    }
    //获取远程文件所采用的方法
    if ($type) {
        $ch = curl_init();
        $timeout = 3;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $img = curl_exec($ch);

        //amazon https://forums.aws.amazon.com/message.jspa?messageID=196878


        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
    }
    //$size=strlen($img);
    //文件大小
    $fp2 = @fopen($save_dir . $filename, 'w');
    fwrite($fp2, $img);
    fclose($fp2);
    unset($img, $url);

    return array('file_name' => $filename, 'save_path' => $save_dir . $filename, 'error' => 0);
}


function generateMagnet($info)
{
    $magnet = false;
    if (isset($info['info_hash'])) {

        $magnet = sprintf('magnet:?xt=urn:btih:%s', strtoupper($info['info_hash']));
        if (isset($info['info']['name'])) {
            $magnet = sprintf('%s&dn=%s', $magnet, $info['info']['name']);
        }
    }

    return $magnet;
}

function success($info_hash)
{
    $result = array('result' => 1, 'url' => $info_hash);
    $json = json_encode($result);
    if ($json) {
        echo $json;
    }
}

function failed()
{
    $result = array('result' => 0, 'url' => null);
    $json = json_encode($result);
    if ($json) {
        echo $json;
    }
}

?>
