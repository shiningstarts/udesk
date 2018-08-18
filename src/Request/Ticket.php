<?php

namespace Udesk\Request;

class Ticket extends Base
{
    /**
     * @desc 获取工单列表
     * @param int $page
     * @param int $per_page
     * @return array|mixed
     * @throws \Exception
     * @package http://www.udesk.cn/doc/apiv2/tickets/#_21
     */
    public function getList($page = 1, $per_page = 20)
    {
        $this->_setUri('tickets');
        $this->_setMethod('GET');
        $this->_setModules('open_api_v1');
        $this->_setQueryData([
            'page' => $page,
            'per_page' => $per_page,
        ]);
        return $this->_sendRequest();
    }
}
