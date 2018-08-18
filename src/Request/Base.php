<?php

namespace Udesk\Request;

use Udesk\Config;
use Udesk\Exception;
use Udesk\Http\HttpClient;

class Base
{
    /**
     * @var string 请求uri
     */
    private $_uri = '';

    /**
     * @var string 请求参数
     */
    private $_method = 'POST';

    /**
     * @var array 请求参数
     */
    private $_data = [];

    /**
     * @var array query参数
     */
    private $_query_data = [];

    /**
     * @var array 不需要鉴权接口
     */
    private $_no_auth_uri = [
        'open_api_v1/log_in'
    ];

    /**
     * @var string 模块
     */
    private $_module = 'open_api_v1';

    /**
     * @var string api版本
     */
    private $_version = 'api2';

    /**
     * @param $module 设置模块名称
     */
    protected function _setModules($module)
    {
        $this->_module = $module;
        //根据模块判断api版本
        switch ($module) {
            case 'api/v2':
            case 'api/v1':
                $this->_version = 'api1';
                break;
            case 'open_api_v1':
                $this->_version = 'api2';
                break;
        }
    }

    /**
     * @desc 设置参数
     * @param $data
     */
    protected function _setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @desc 设置query参数
     * @param $data
     */
    protected function _setQueryData($data)
    {
        $this->_query_data = $data;
    }

    /**
     * @desc 设置请求方式
     * @param $method
     */
    protected function _setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * @desc 设置uri
     * @param $uri string
     * @param $rest string
     */
    protected function _setUri($uri, $rest = '')
    {
        $this->_uri = sprintf("%s/%s", $uri, $rest);
    }

    /**
     * @desc 发送请求
     * @return array|mixed
     * @throws \Exception
     */
    protected function _sendRequest()
    {
        $this->_validParams();
        $this->_initParams();

        $httpClient = new HttpClient();
        //设置uri
        $httpClient->setUri($this->_uri);
        //设置query数据
        $this->_query_data && $httpClient->setQueryData($this->_query_data);
        //设置post数据
        $this->_data && $httpClient->setData($this->_data);
        //设置公司
        $httpClient->setCompany(Config::COMPANY);
        //设置模块
        $this->_module && $httpClient->setModule($this->_module);
        //设置请求方法
        $httpClient->setMethod($this->_method);
        //发送请求
        return $httpClient->sendRequest();
    }

    /**
     * @desc 参数初始化
     */
    protected function _initParams()
    {
        !in_array($this->_uri, $this->_no_auth_uri) && $this->_initSign();
    }

    /**
     * @desc 签名初始化
     */
    protected function _initSign()
    {
        //todo timezone set
        date_default_timezone_set('PRC');
        switch ($this->_version) {
            case 'api1':
                $this->_query_data['sign'] = $this->_generateSignV1();
                break;
            case 'api2':
                $this->_query_data['email'] = Config::EMAIL;
                $this->_query_data['timestamp'] = time();
                $this->_query_data['sign'] = $this->_generateSingV2();
                break;
        }
    }

    /**
     * @desc 生产签名V2
     * @return string
     */
    protected function _generateSingV2()
    {
        return sha1(sprintf("%s&%s&%s", $this->_query_data['email'], Config::OPEN_API_AUTH_TOKEN, $this->_query_data['timestamp']));
    }

    /**
     * @desc 生产签名V1
     * @return string
     */
    protected function _generateSignV1()
    {
        return md5(sprintf("%s&%s", urldecode(http_build_query($this->_query_data ?? $this->_data)), Config::SECRET));
    }


    /**
     * @desc 验证参数是否为空
     * @throws \Exception
     */
    protected function _validParams()
    {
        $this->_validParamsEmpty([
            'uri' => $this->_uri,
            'method' => $this->_method,
        ]);
    }


    /**
     * @desc 验证多个参数是否为空
     * @param $params array
     * @throws \Exception
     */
    private function _validParamsEmpty($params)
    {
        foreach ($params as $key => $value) {
            $this->_validEmpty($key, $value);
        }
    }

    /**
     * @desc 验证参数是否为空
     * @param $key string
     * @param $value mixed|string|array
     * @throws \Exception
     */
    private function _validEmpty($key, $value)
    {
        empty($value) && !is_numeric($value) && Exception::throwException(20001, sprintf("参数%s为空", $key));
    }
}
