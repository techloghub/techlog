<?php
class BaiduPanGateway
{
    /**
     * 预上传接口
     * https://pan.baidu.com/union/document/upload#%E9%A2%84%E4%B8%8A%E4%BC%A0
     */
    public function precreate($localpath, $remotepath) {
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file?method=precreate';

        $size = filesize($localpath);
        $fp = fopen($localpath, 'rb');
        $md5s = array();
        while (!feof($fp)) {
            $md5s[] = md5(fread($fp, 4096 * 1000));
        }
        $params = array('path' => $remotepath, 'size' => $size, 'isdir' => 0, 'autoinit' => 1,
            'block_list' => json_encode($md5s));
        $response = HttpCurl::post($url, $params);
        var_dump($response);
    }
}