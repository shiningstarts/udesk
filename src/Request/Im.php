<?php

namespace Udesk\Request;

class Im extends Base
{
    /**
     * @param $start_time datetime 开始时间
     * @param $end_time datetime 结束时间
     * @param int $page 页码
     * @param int $per_page 每页大小
     * @param string $status 会话状态
     * @return array|mixed
     * @throws \Exception
     */
    public function getList($start_time, $end_time, $page = 1, $per_page = 20, $status = 'close')
    {

        $this->_setUri('im/session');
        $this->_setMethod('GET');
        $this->_setModules('api/v2');
        $this->_setQueryData([
            'page' => $page,
            'per_page' => $per_page,
//            'status' => $status,
//            'start_time' => $start_time,
//            'end_time' => $end_time,
        ]);
        return $this->_sendRequest();
    }
}
