<?php
namespace Web2All\Softwear\Model\ResourceModel\SyncState;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Web2All\Softwear\Model\SyncState','Web2All\Softwear\Model\ResourceModel\SyncState');
    }
}
