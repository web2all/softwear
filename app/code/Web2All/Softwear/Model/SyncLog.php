<?php
namespace Web2All\Softwear\Model;

class SyncLog extends \Magento\Framework\Model\AbstractModel implements \Web2All\Softwear\Api\Data\SyncLogInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'web2all_softwear_synclog';

    protected function _construct()
    {
        $this->_init('Web2All\Softwear\Model\ResourceModel\SyncLog');
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
     * Get the completed status
     *
     * @return boolean
     */
    public function getIsCompleted()
    {
        return $this->getData('is_completed');
    }
    
    /**
     * Set the completed status
     *
     * @param boolean $completed
     */
    public function setIsCompleted($completed)
    {
        $this->setData('is_completed', $completed);
    }
    
    /**
     * Get the error status (true when errors occured)
     *
     * @return boolean
     */
    public function getHasErrors()
    {
        return $this->getData('has_errors');
    }
    
    /**
     * Set the error status
     *
     * @param boolean $errors
     */
    public function setHasErrors($errors)
    {
        $this->setData('has_errors', $errors);
    }
    
    /**
     * Get the logdata
     *
     * @return string
     */
    public function getLogData()
    {
        return $this->getData('log_data');
    }
    
    /**
     * Set the logdata
     *
     * Can be a lot of text
     *
     * @param string $data
     */
    public function setLogData($data)
    {
        $this->setData('log_data', $data);
    }
    
    /**
     * Get the number of updated products
     *
     * @return int
     */
    public function getNumProductsUpdated()
    {
        return $this->getData('num_products_updated');
    }
    
    /**
     * Set the number of updated products
     *
     * @param int $num
     */
    public function setNumProductsUpdated($num)
    {
        $this->setData('num_products_updated', $num);
    }
    
    /**
     * Get the number of processed products
     *
     * @return int
     */
    public function getNumProductsProcessed()
    {
        return $this->getData('num_products_processed');
    }
    
    /**
     * Set the number of processed products
     *
     * @param int $num
     */
    public function setNumProductsProcessed($num)
    {
        $this->setData('num_products_processed', $num);
    }
}
