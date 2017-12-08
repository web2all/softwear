<?php
namespace Web2All\Softwear\Model;
class SyncState extends \Magento\Framework\Model\AbstractModel implements \Web2All\Softwear\Api\Data\SyncStateInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'web2all_softwear_syncstate';

    protected function _construct()
    {
        $this->_init('Web2All\Softwear\Model\ResourceModel\SyncState');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
    /**
     * Get the Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData('id');
    }
    
    /**
     * Set the Id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->setData('id', $id);
    }
    
    /**
     * Get the running status
     *
     * @return boolean
     */
    public function getIsRunning()
    {
        return $this->getData('is_running');
    }
    
    /**
     * Set the running status (true if running)
     *
     * @param boolean $running
     */
    public function setIsRunning($running)
    {
        $this->setData('is_running', $running);
    }
    
    /**
     * Get the start from beginning state
     *
     * When true then the next Sync must start from the beginning,
     * if false then it must start from getStartPage().
     *
     * @return boolean
     */
    public function getStartBeginning()
    {
        return $this->getData('start_beginning');
    }
    
    /**
     * Set the start from beginning state
     *
     * @param boolean $startbeginning
     */
    public function setStartBeginning($startbeginning)
    {
        $this->setData('start_beginning', $startbeginning);
    }
    
    /**
     * Get the starting page
     *
     * @return int
     */
    public function getStartPage()
    {
        return $this->getData('start_page');
    }
    
    /**
     * Set the starting page
     *
     * The first page is 1.
     *
     * @param int $page
     */
    public function setStartPage($page)
    {
        $this->setData('start_page', $page);
    }
}
