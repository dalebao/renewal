<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 下午3:58
 */

if (!function_exists('app')) {
    /**
     * @param $service
     * @param string $key
     * @return mixed
     */
    function app($service, $key = '')
    {
        $container = new \App\Utils\ServiceContainer();
        return $container->$service->getInstance($key);
    }
}

if (!function_exists('decrypt_6d')) {
    function decrypt_6d($data)
    {

        if ($data != '') {
            $encryptedData = fromHexString($data);
            $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, "5k3f4good\0\0\0\0\0\0\0", $encryptedData, MCRYPT_MODE_CBC, '0102030405060708');
            //未加密的数据直接返回
            if (preg_match('/([\d-]{5,})|(?!(\.|-|_))(?![a-zA-Z0-9\.\-_]*(\.|-|_)@)[a-zA-Z0-9\.\-_]+@(?!.{64,}\.)(?![\-_])(?![a-zA-Z0-9\-_]*[\-_]\.)[a-zA-Z0-9\-_]+(\.\w+)+\-*\d*$/', trim($decrypted))) {
                return trim($decrypted);
            } else {
                file_put_contents('/tmp/bxl_debug_' . date('Ymd') . '.log', '[line: ' . __LINE__ . ']' . '[data]' . var_export($decrypted, true) . PHP_EOL, FILE_APPEND);
                file_put_contents('/tmp/bxl_debug_' . date('Ymd') . '.log', '[line: ' . __LINE__ . ']' . '[data]' . var_export($data, true) . PHP_EOL, FILE_APPEND);

                return trim($data);
            }
        }
        return $data;
    }
}


if (!function_exists('fromHexString')) {
    /*
     * @function fromHexString 把十六进制数转换成字符串
     */
    function fromHexString($sa)
    {
        $buf = "";
        for ($i = 0; $i < strlen($sa); $i += 2) {
            $val = chr(hexdec(substr($sa, $i, 2)));
            $buf .= $val;
        }
        return $buf;
    }
}

if (!function_exists('curl_post')) {
    function curl_post($url, $data, $header = "")
    {
        if (is_array($data)) {
            $data = http_build_query($data, '&');
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//https
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if ($header != "")
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode != 201 && $httpCode != 200) return $httpCode;
        return ($result);
    }
}