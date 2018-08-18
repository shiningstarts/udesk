<?php

namespace Udesk;

/**
 * Class Exception
 * @desc 异常处理类
 * @package Udesk\Exception
 * @author jumao
 */
class Exception
{
    /**
     * @desc 抛出异常
     * @param int $code
     * @param string $message
     * @throws \Exception
     */
    public static function throwException($code = 20000, $message = '')
    {
        $message = $message ?? static::_getErrorMsgByCode($code);
        throw new \Exception($message, $code);
    }

    /**
     * @desc 通过错误码获取错误信息
     * @param $code
     * @return mixed
     */
    private static function _getErrorMsgByCode($code)
    {
        return isset(static::ERROR_MSG[$code]) ? static::ERROR_MSG[$code] : static::ERROR_MSG[20000];
    }

    //错误信息对照表
    const ERROR_MSG = [
        20000 => '未知错误',
        20001 => '参数错误',
    ];
}
