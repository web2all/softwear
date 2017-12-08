<?php
namespace Web2All\Softwear\Setup;
class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    
    /**
     * SyncStateFactory
     *
     * @var \Web2All\Softwear\Model\SyncStateFactory
     */
    protected $_syncStateFactory;
    
    /**
     * SyncStateRepository
     *
     * @var \Web2All\Softwear\Model\SyncStateRepository
     */
    protected $_syncStateRepository;
    
    /**
     * Constructor
     *
     * @param \Web2All\Softwear\Model\SyncStateFactory $syncStateFactory
     * @param \Web2All\Softwear\Api\SyncStateRepositoryInterface $syncStateRepository
     */
    public function __construct(
        \Web2All\Softwear\Model\SyncStateFactory $syncStateFactory,
        \Web2All\Softwear\Api\SyncStateRepositoryInterface $syncStateRepository) 
    {
        $this->_syncStateFactory=$syncStateFactory;
        $this->_syncStateRepository=$syncStateRepository;
    }
    
    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        // web2all_softwear_syncstate must have a single record, we use it for tracking sync state
        $newSyncstate=$this->_syncStateFactory->create();
        $newSyncstate->setData('is_running',0);
        $newSyncstate->setData('start_beginning',1);
        $newSyncstate->setData('start_page',1);
        $this->_syncStateRepository->save($newSyncstate);
    }
}
