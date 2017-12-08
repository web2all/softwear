<?php
namespace Web2All\Softwear\Api\Data;
interface SyncStateInterface 
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
     * Get the running status
     *
     * @return boolean
     */
    public function getIsRunning();
    
    /**
     * Set the running status (true if running)
     *
     * @param boolean $running
     */
    public function setIsRunning($running);
    
    /**
     * Get the start from beginning state
     *
     * When true then the next Sync must start from the beginning,
     * if false then it must start from getStartPage().
     *
     * @return boolean
     */
    public function getStartBeginning();
    
    /**
     * Set the start from beginning state
     *
     * @param boolean $startbeginning
     */
    public function setStartBeginning($startbeginning);
    
    /**
     * Get the starting page
     *
     * @return int
     */
    public function getStartPage();
    
    /**
     * Set the starting page
     *
     * The first page is 1.
     *
     * @param int $page
     */
    public function setStartPage($page);
    
}