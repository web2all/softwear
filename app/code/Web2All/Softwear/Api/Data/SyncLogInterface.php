<?php
namespace Web2All\Softwear\Api\Data;

interface SyncLogInterface 
{
    /**
     * Get the Id
     *
     * @return int
     */
    public function getId();
    
    /**
     * Set the Id
     *
     * @param int $id
     */
    public function setId($id);
    
    /**
     * Get the completed status
     *
     * @return boolean
     */
    public function getIsCompleted();
    
    /**
     * Set the completed status
     *
     * @param boolean $completed
     */
    public function setIsCompleted($completed);
    
    /**
     * Get the error status (true when errors occured)
     *
     * @return boolean
     */
    public function getHasErrors();
    
    /**
     * Set the error status
     *
     * @param boolean $errors
     */
    public function setHasErrors($errors);
    
    /**
     * Get the logdata
     *
     * @return string
     */
    public function getLogData();
    
    /**
     * Set the logdata
     *
     * Can be a lot of text
     *
     * @param string $data
     */
    public function setLogData($data);
    
    /**
     * Get the number of updated products
     *
     * @return int
     */
    public function getNumProductsUpdated();
    
    /**
     * Set the number of updated products
     *
     * @param int $num
     */
    public function setNumProductsUpdated($num);
    
    /**
     * Get the number of processed products
     *
     * @return int
     */
    public function getNumProductsProcessed();
    
    /**
     * Set the number of processed products
     *
     * @param int $num
     */
    public function setNumProductsProcessed($num);
}