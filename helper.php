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
        $res = [];
        foreach ($data as $key => $val) {
            $msg = $val;
            if ($msg != '') {
                $encryptedData = fromHexString($msg);
                $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, "5k3f4good\0\0\0\0\0\0\0", $encryptedData, MCRYPT_MODE_CBC, '0102030405060708');

                //未加密的数据直接返回
                if (preg_match('/([\d-]{5,})|(?!(\.|-|_))(?![a-zA-Z0-9\.\-_]*(\.|-|_)@)[a-zA-Z0-9\.\-_]+@(?!.{64,}\.)(?![\-_])(?![a-zA-Z0-9\-_]*[\-_]\.)[a-zA-Z0-9\-_]+(\.\w+)+$/', trim($decrypted))) {
                    $res[$key] = trim($decrypted);
                } else {
                    $res[$key] = trim($msg);
                }
            } else {
                $res[$key] = trim($msg);
            }
        }
        return $res;
    }
}