<?php
namespace Web2All\Softwear\Model\ResourceModel;
class SyncState extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('web2all_softwear_syncstate','id');
    }
}
