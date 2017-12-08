<?php
namespace Web2All\Softwear\Model\ResourceModel\SyncLog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Web2All\Softwear\Model\SyncLog','Web2All\Softwear\Model\ResourceModel\SyncLog');
    }
}
