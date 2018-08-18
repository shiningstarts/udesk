<?php

namespace Udesk\Http;

use Udesk\Exception;

class HttpClient
{
    /**
     * @var string 请求方式
     */
    private $_method = 'POST';

    /**
     * @var bool 是否http
     */
    private $_is_https = false;

    /**
     * @var string  请求地址
     */
    private $_uri = '';

    /**
     * @var string 主域名
     */
    private $_master_host = 'udesk.cn';

    /**
     * @var string 公司名称
     */
    private $_company = '';

    /**
     * @var bool 是否验证ssl
     */
    private $_ssl_verification = false;

    /**
     * @var array 请求参数
     */
    private $_data = [];

    /**
     * @var array 请求query基本参数
     */
    private $_query_data = [];

    /**
     * @var string 请求header
     */
    private $_header = 'content-type: application/json; charset=utf-8';

    /**
     * @var string 模块名称
     */
    private $_module = '';

    /**
     * @var string api版本
     */
    private $_version = 'api2';

    /**
     * @param $version
     * @throws \Exception
     */
    public function setVersion($version)
    {
        $this->_validEmpty('version', $version);
        if (!in_array($version, ['api1', 'api2'])) {
            Exception::throwException(20001, 'api版本错误');
        }
        $this->_version = $version;
    }

    /**
     * @desc 设置模块名称
     * @param $module
     * @throws \Exception
     */
    public function setModule($module)
    {
        $this->_validEmpty('module', $module) || $this->_module = $module;
        //根据模块判断api版本
        switch ($module) {
            case 'api/v2':
            case 'api/v1':
                $this->setVersion('api1');
                break;
            case 'open_api_v1':
                $this->setVersion('api2');
                break;
        }
    }


    /**
     * @desc 设置请求方式
     * @param $method
     * @throws \Exception
     */
    public function setMethod($method)
    {
        $this->_validEmpty('method', $method);
        $method = strtoupper($method);
        if (!in_array($method, ['POST', 'PUT', 'GET', 'DELETE'])) {
            Exception::throwException(20001, 'http请求方式错误，只支持GET,POST,PUT,DELETE');
        }
        $this->_method = $method;
    }

    /**
     * @desc 设置请求地址
     * @param $uri
     * @throws \Exception
     */
    public function setUri($uri)
    {
        $this->_validEmpty('uri', $uri) || $this->_uri = $uri;
    }

    /**
     * @desc 请求参数
     * @param $query_data
     * @throws \Exception
     */
    public function setQueryData($query_data)
    {
        $this->_validEmpty('query_data', $query_data) || $this->_query_data = $query_data;
    }

    /**
     * @desc query基本请求参数
     * @param $data
     * @throws \Exception
     */
    public function setData($data)
    {
        $this->_validEmpty('data', $data) || $this->_data = $data;
    }

    /**
     * @desc 设置https
     */
    public function setHttps()
    {
        $this->_is_https = true;
    }

    /**
     * @desc 设置http
     */
    public function setHttp()
    {
        $this->_is_https = false;
    }

    public function setCompany($company)
    {
        $this->_validEmpty('company', $company) || $this->_company = $company;
    }

    /**
     * @desc 发送请求
     * @return array|mixed
     * @throws \Exception
     */
    public function sendRequest()
    {
        $this->_validRequestParams();
        return $this->_sendRequest();
    }

    /**
     * @desc 格式化url地址
     * @param $url
     * @return mixed
     */
    private function _formatUrl($url)
    {
        return str_replace(' ', '%20', urldecode($url));
    }

    /**
     * @desc 发送请求
     * @return mixed|array
     * @throws \Exception
     */
    private function _sendRequest()
    {
        $url = sprintf("%s.%s/%s/%s?%s", $this->_company, $this->_master_host, $this->_module, $this->_uri, http_build_query($this->_query_data));
        $url = $this->_formatUrl($url);
        $postFile = http_build_query($this->_data);

        $curlHandle = curl_init();
        // Verification of the SSL cert
        if ($this->_ssl_verification) {
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_HEADER, $this->_header);
        $postFile && curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postFile);

        switch ($this->_method) {
            case 'POST':
                curl_setopt($curlHandle, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        $response = json_decode(curl_exec($curlHandle), true);

        //curl错误判断
        if (curl_errno($curlHandle)) {
            $error = curl_error($curlHandle);
            curl_close($curlHandle);
            Exception::throwException(20000, $error);
        }
        curl_close($curlHandle);

        //结果验证
        return $this->_validResponse($response);

    }

    /**
     * @desc 请求结果验证
     * @param $response
     * @return array|mixed
     * @throws \Exception
     */
    private function _validResponse($response)
    {
        if (!is_array($response)) {
            Exception::throwException(20000, '返回数据格式不是json');
        }
        $result = $response;
        switch ($this->_version) {
            case 'api1':
                if (!isset($response['status'])) {
                    Exception::throwException(20000, '返回数据格式有误');
                }
                if ($response['status'] != 0) {
                    Exception::throwException(20000, $response['message']);
                }
                break;
            case 'api2':
                if (!isset($response['code'])) {
                    Exception::throwException(20000, '返回数据格式有误');
                }
                if ($response['code'] != 1000) {
                    Exception::throwException(20000, $response['message']);
                }
                $result = $response['contents'];
        }
        return $result;
    }

    /**
     * @desc 验证请求参数是否为空
     * @throws \Exception
     */
    private function _validRequestParams()
    {
        $this->_validParamsEmpty([
            'uri' => $this->_uri,
        ]);
        if (empty($this->_data) && empty($this->_query_data)) {
            Exception::throwException(20001, 'data和query_data不能同时为空');
        }

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
