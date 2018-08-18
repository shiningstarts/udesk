<?php

namespace Udesk\Request;

use Udesk\Config;

/**
 * @desc 通用接口
 * Class Common
 * @package Udesk\Request
 */
class Common extends Base
{
    /**
     * @desc 获取token
     * @return array|mixed
     * @throws \Exception
     */
    public function getToken()
    {
        $this->_setUri('open_api_v1/log_in');
        $this->_setData([
            'email' => Config::EMAIL,
            'password' => Config::PASSWORD,
        ]);
        return $this->_sendRequest();
    }
}
