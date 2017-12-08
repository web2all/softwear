<?php
/**
 * Copyright © 2016 Web2All B.V.. All rights reserved.
 * See LICENCE.txt for license details.
 */

namespace Web2All\Softwear\Cron;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Psr\Log\LoggerInterface;

class StockUpdater
{

    /**
     * scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    /**
     * Sync object
     *
     * @var \Web2All\Softwear\Model\StockUpdate\SoftwearSync
     */
    protected $_softwearSync;

    /**
     * Constructor
     * 
     * @param \Web2All\Softwear\Model\StockUpdate\SoftwearSync $softwearSync
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Web2All\Softwear\Model\StockUpdate\SoftwearSync $softwearSync,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Psr\Log\LoggerInterface $logger) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_softwearSync= $softwearSync;
    }
    
    public function execute()
    {
        $this->_logger->debug('Web2All\Softwear\Cron\StockUpdater->execute: called');
        
        $this->_softwearSync->syncProducts();
        
        $this->_logger->debug('Web2All\Softwear\Cron\StockUpdater->execute: done');
        return true;
    }
}
