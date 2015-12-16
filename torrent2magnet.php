<?php
$verifyToken = md5('unique_salt' . $_POST['timestamp']);
if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
    $tempFile = $_FILES['Filedata']['tmp_name'];

    // Validate the file type
    $fileTypes = array('torrent');
    $fileParts = pathinfo($_FILES['Filedata']['name']);

    if (in_array($fileParts['extension'], $fileTypes)) {
        require('lightbenc.php');
        $info = Lightbenc::bdecode_getinfo($tempFile);

        if ($magnet = generateMagnet($info)) {
            success($magnet);
        } else {
            failed();
        }
    } else {
        failed();
    }
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
