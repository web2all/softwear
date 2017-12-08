<?php
/**
 * Copyright © 2016 Web2All B.V.. All rights reserved.
 * See LICENCE.txt for license details.
 */

namespace Web2All\Softwear\Model\StockUpdate;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Psr\Log\LoggerInterface;

/**
 */
class SoftwearSync
{
  
    /**
     * SyncStateRepository
     *
     * @var \Web2All\Softwear\Model\SyncStateRepository
     */
    protected $_syncStateRepository;
    
    /**
     * SyncLogFactory
     *
     * @var \Web2All\Softwear\Model\SyncLogFactory
     */
    protected $_syncLogFactory;
    
    /**
     * SyncLogRepository implementing class
     * 
     * implemented in \Web2All\Softwear\Model\SyncLogRepository 
     * 
     * @var \Web2All\Softwear\Api\SyncLogRepositoryInterface
     */
    protected $_syncLogRepository;
    
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
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Product repository factory
     *
     * @var \Magento\Catalog\Model\ProductRepositoryFactory
     */
    protected $_productRepositoryFactory;

    /**
     * Builder to create search filters
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * Something for getting stock?
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * EAV Attribute
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * Softwear SWAPI
     *
     * @var \Web2All\Softwear\Model\Softwear\Api
     */
    protected $_softwearApi;

    /**
     * Prepend to every log message
     *
     * @var string
     */
    protected $_logPrefix;

    /**
     * How many configurable products to process at a time
     *
     * @var int
     */
    protected $_configurableBatchSize;

    /**
     * The magento attribute name of the product eav attribute which contains
     * the softwear SKU code.
     *
     * @var string
     */
    protected $_magentoAttributeNameSoftwearSku;

    /**
     * The magento attribute name of the product eav attribute which contains
     * the softwear 'Key' articlecode.
     *
     * @var string
     */
    protected $_magentoAttributeNameSoftwearKey;

    /**
     * Is the sync enabled (in the admin store configuration)
     *
     * @var boolean
     */
    protected $_syncEnabled;

    /**
     * Current SyncLog object
     *
     * @var \Web2All\Softwear\Model\SyncLog
     */
    protected $_syncLogEntry;

    /**
     * Current SyncState
     *
     * @var \Web2All\Softwear\Model\SyncState
     */
    protected $_syncState;

    /**
     * Loglevel [0-3]
     *
     * @var int
     */
    protected $_logLevel;

    /**
     * Dryrun mode (if true do not update stock)
     *
     * @var boolean
     */
    protected $_dryRun;

    /**
     * Max runtime in seconds
     * 
     * Script stops after this limit but requires a few seconds margin.
     *
     * @var int
     */
    protected $_maxRunTime;

    /**
     * Constructor
     *
     * @param \Web2All\Softwear\Api\SyncStateRepositoryInterface $syncStateRepository
     * @param \Web2All\Softwear\Model\SyncLogFactory $syncLogFactory
     * @param \Web2All\Softwear\Api\SyncLogRepositoryInterface $syncLogRepository
     * @param \Web2All\Softwear\Model\Softwear\Api $softwearApi
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductRepositoryFactory $productRepositoryFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Web2All\Softwear\Api\SyncStateRepositoryInterface $syncStateRepository,
        \Web2All\Softwear\Model\SyncLogFactory $syncLogFactory,
        \Web2All\Softwear\Api\SyncLogRepositoryInterface $syncLogRepository,
        \Web2All\Softwear\Model\Softwear\Api $softwearApi,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductRepositoryFactory $productRepositoryFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Psr\Log\LoggerInterface $logger) 
    {
        $this->_syncStateRepository=$syncStateRepository;
        $this->_syncLogFactory = $syncLogFactory;
        $this->_syncLogRepository = $syncLogRepository;
        $this->_softwearApi = $softwearApi;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_productCollectionFactory= $productCollectionFactory;
        $this->_productRepositoryFactory= $productRepositoryFactory;
        $this->_searchCriteriaBuilder= $searchCriteriaBuilder;
        $this->_eavAttribute= $eavAttribute;
        $this->_stockRegistry= $stockRegistry;
        
        $this->_magentoAttributeNameSoftwearSku=$this->_scopeConfig->getValue('web2all_softwear/general/softwear_product_attribute_sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_magentoAttributeNameSoftwearKey=$this->_scopeConfig->getValue('web2all_softwear/general/softwear_product_attribute_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $this->_syncEnabled=$this->_scopeConfig->getValue('web2all_softwear/general/enable_sync', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $this->_logLevel=(int)$this->_scopeConfig->getValue('web2all_softwear/loggingerrors/loglevel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($this->_logLevel<0 || $this->_logLevel>3){
            // invalid setting, default to 1
            $this->_logLevel=1;
        }
        
        $this->_dryRun=false;
        if($this->_scopeConfig->getValue('web2all_softwear/loggingerrors/dryrun', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
            $this->_dryRun=true;
        }
        
        $this->_logPrefix='Web2All\Softwear\Model\StockUpdate\SoftwearSync';
        
        // 20min is 1200sec
        $this->_maxRunTime=1200;
        
        // set _configurableBatchSize higher for more efficiency at memory cost
        $this->_configurableBatchSize=2;
    }
    
    /**
     * Sync magento products with stock from Softwear API
     *
     */
    public function syncProducts()
    {
        if($this->_logLevel){
            $this->_logger->debug($this->_logPrefix.'->syncProducts: called');
            if($this->_dryRun){
                $this->_logger->debug($this->_logPrefix.'->syncProducts: running in dryrun mode, no stocklevels are updated!');
            }
        }
        
        $startTime=time();
        
        // add SyncLog entry
        $this->createSyncLog();
        
        if(!$this->_syncEnabled){
            // sync has been disabled in admin store configuration
            $this->addSyncLogMessage('Sync disabled');
            if($this->_logLevel){
                $this->_logger->debug($this->_logPrefix.'->syncProducts: done (Sync disabled)');
            }
            return;
        }
        
        // check current syncstate
        $syncState=$this->getSyncState();
        if($syncState->getIsRunning()){
            // already running
            // check if we have to bypass the stillrunning check
            if(!$this->_scopeConfig->getValue('web2all_softwear/loggingerrors/recover_stillrunning', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
                $this->addSyncLogMessage('Previous still running');
                if($this->_logLevel){
                    $this->_logger->debug($this->_logPrefix.'->syncProducts: done (Previous still running)');
                }
                return;
            }else{
                $this->addSyncLogMessage('Previous still running, but Recover crash is enabled in configuration');
                if($this->_logLevel){
                    $this->_logger->debug($this->_logPrefix.'->syncProducts: Previous still running but we ignore runningstate');
                }
            }
        }
        // check if we have to start at an alternative page
        $currentPage=1;
        if(!$syncState->getStartBeginning()){
            $currentPage=$syncState->getStartPage();
            if($this->_logLevel){
                $this->_logger->debug($this->_logPrefix.'->syncProducts: continueing last run from page '.$currentPage);
            }
        }
        
        // check if all config entries we need are set
        if(!$this->_magentoAttributeNameSoftwearKey){
          $this->addSyncLogMessage('Fatal error: No configuration present for web2all_softwear/general/softwear_product_attribute_key');
          throw new \Exception('No configuration present for web2all_softwear/general/softwear_product_attribute_key');
        }
        if(!$this->_magentoAttributeNameSoftwearSku){
          $this->addSyncLogMessage('Fatal error: No configuration present forweb2all_softwear/general/softwear_product_attribute_sku');
          throw new \Exception('No configuration present forweb2all_softwear/general/softwear_product_attribute_sku');
        }
        
        // get attribute id's of custom eav attributes we need
        $softwearProductAttributeKey=$this->_eavAttribute->getIdByCode('catalog_product',$this->_magentoAttributeNameSoftwearKey);
        $softwearProductAttributeSku=$this->_eavAttribute->getIdByCode('catalog_product',$this->_magentoAttributeNameSoftwearSku);
        
        // check if we could find the custom product attributes
        if(!$softwearProductAttributeKey){
          $this->addSyncLogMessage('Fatal error: Could not find attribute id for eav attribute '.$this->_magentoAttributeNameSoftwearKey);
          throw new \Exception('Could not find attribute id for eav attribute '.$this->_magentoAttributeNameSoftwearKey);
        }
        if(!$softwearProductAttributeSku){
          $this->addSyncLogMessage('Fatal error: Could not find attribute id for eav attribute '.$this->_magentoAttributeNameSoftwearSku);
          throw new \Exception('Could not find attribute id for eav attribute '.$this->_magentoAttributeNameSoftwearSku);
        }
        
        // ok set our state to running
        $this->setSyncState(true, true, 1);
        
        if($this->_logLevel>2){
            $this->_logger->debug($this->_logPrefix.'->syncProducts: attribute mapping: '.$this->_magentoAttributeNameSoftwearKey.'='.$softwearProductAttributeKey.' '.$this->_magentoAttributeNameSoftwearSku.'='.$softwearProductAttributeSku);
        }
        
        // init counters
        $configurableProductCount=0;
        $simpleProductCount=0;
        
        $configurableErrorCount=0;
        $simpleErrorCount=0;
        
        $stockUpdateCount=0;
        
        $continue = true;
        $runtimeExceeded = false;
        
        // then start a loop to fetch configurable products in page sized batches
        while($continue){
            // slow down a bit
            sleep(1);
            
            // test if we near our runtime
            if($this->_maxRunTime && $this->_maxRunTime < (time()-$startTime)){
                // we exceeded our runtime
                if($this->_logLevel){
                    $this->_logger->debug($this->_logPrefix.'->syncProducts: exceeded our runtime of '.$this->_maxRunTime.' seconds. Next run must start at page '.$currentPage);
                }
                $this->addSyncLogMessage('Exceeded our runtime of '.$this->_maxRunTime.' seconds. Next run must start at page '.$currentPage);
                $runtimeExceeded = true;
                $continue = false;
                continue;
            }
            
            // get a batch of configurable products
            $this->_searchCriteriaBuilder->setCurrentPage($currentPage);
            $this->_searchCriteriaBuilder->setPageSize($this->_configurableBatchSize);
            $this->_searchCriteriaBuilder->addFilter('type_id', 'configurable');
            $searchCriteria = $this->_searchCriteriaBuilder->create();
            $productRepository=$this->_productRepositoryFactory->create();
            $products = $productRepository->getList($searchCriteria);
            if($this->_logLevel>2){
                $this->_logger->debug($this->_logPrefix.'->syncProducts: get page "'.$searchCriteria->getCurrentPage().'" getTotalCount: "'.$products->getTotalCount().'" count(getItems): "'.count($products->getItems()).'" / "'.$searchCriteria->getPageSize().'"');
            }
            // Unfortunately magento doesn't provide an easy way to detect the last page.
            // When you request a page greater than the last page, you will get the content of the last page.
            // This is very inconvenient because now we have to calculate ourselves what the last page should be...
            if ($products->getTotalCount() <= ($searchCriteria->getPageSize()*$currentPage)) {
                // its the last page, we will process this page but after bail out
                $continue = false;
            }
            $skuList=array();
            $configurableProductList=array();
            foreach($products->getItems() as $configurableProduct){
                // $configurable_product is a \Magento\Catalog\Model\Product
                // but in reality its a \Magento\Catalog\Model\Product\Interceptor which is a wrapper for plugins 
                // to extend the Product
                
                // should be configurable products only
                if ($configurableProduct->getTypeId() != 'configurable') {
                    $this->addSyncLogMessage('Fatal error: expected only configurable products but got: '.$configurableProduct->getTypeId());
                    
                    // ok set our state to stopped (and next time start at next page, skip this one)
                    $this->setSyncState(false, false, $currentPage+1);
                    
                    throw new \Exception($this->_logPrefix.'->syncProducts: expected only configurable products but got: '.$configurableProduct->getTypeId());
                }
                
                $configurableProductCount++;
                
                $softwearArticlecodeAttr = $configurableProduct->getCustomAttribute($this->_magentoAttributeNameSoftwearSku);
                $softwearArticlecode='';
                if($softwearArticlecodeAttr){
                    $softwearArticlecode = $softwearArticlecodeAttr->getValue();
                }
                if(!$softwearArticlecode){
                    if($this->_logLevel){
                        $this->_logger->debug($this->_logPrefix.'->syncProducts: configurable Product "'.$configurableProduct->getId().'" with sku: "'.$configurableProduct->getSku().'" has NO '.$this->_magentoAttributeNameSoftwearSku.' attribute');
                    }
                    // maybe in the future see if we can inform shop owner
                    $this->addSyncLogMessage('Warning: configurable Product "'.$configurableProduct->getId().'" with sku: "'.$configurableProduct->getSku().'" has NO '.$this->_magentoAttributeNameSoftwearSku.' attribute');
                    $configurableErrorCount++;
                    continue;
                }
                $skuList[]=$softwearArticlecode;
                $configurableProductList[]=$configurableProduct;
            }
            
            try {
                $stockLevels=$this->_softwearApi->getStock($skuList);
            } catch (\Exception $e) {
                // failed to get stock levels, skip this batch
                if($this->_logLevel){
                    $this->_logger->debug($this->_logPrefix.'->syncProducts: failed to get stocklist for "'.implode(',',$skuList).'", error: '.$e->getMessage());
                    $this->_logger->debug($this->_logPrefix.'->syncProducts: skipping "'.implode(',',$skuList).'"');
                }
                $configurableErrorCount+=count($skuList);
                $this->addSyncLogMessage('Warning: failed to get stocklist for "'.implode(',',$skuList).'", error: '.$e->getMessage());
                $currentPage++;
                continue;
            }
            
            // debuglog the stocklevels
            $stockLevelsFlat='';
            foreach($stockLevels as $tmpKey => $tmpValue){
                $stockLevelsFlat.=' '.$tmpKey.':'.$tmpValue;
            }
            if($this->_logLevel>2){
                $this->_logger->debug($this->_logPrefix.'->syncProducts: stocklist for: "'.implode(',',$skuList).'" '.$stockLevelsFlat);
            }
            
            // now loop the configurable products again, this time to process the child (simple) products
            foreach($configurableProductList as $configurableProduct){
                // $configurable_product is a \Magento\Catalog\Model\Product
                // but in reality its a \Magento\Catalog\Model\Product\Interceptor which is a wrapper for plugins 
                // to extend the Product
                
                // $productTypeInstance \Magento\ConfigurableProduct\Model\Product\Type\Configurable
                $productTypeInstance=$configurableProduct->getTypeInstance();
                // we have to add our custom attributes (by default only standard user visible attributes are selected)
                $attrIds=$productTypeInstance->getUsedProductAttributeIds($configurableProduct);
                $attrIds[]=$softwearProductAttributeKey;
                $attrIds[]=$softwearProductAttributeSku;
                $productTypeInstance->setUsedProductAttributes($configurableProduct,$attrIds);
                // getUsedProducts has optional param requiredAttributeIds but its not used!
                $childProducts = $productTypeInstance->getUsedProducts($configurableProduct);
                // now process each child product of this configuarble product. They should be simple products.
                foreach ($childProducts as $item){
                    // $item is a \Magento\Catalog\Model\Product
                    // but in reality its a \Magento\Catalog\Model\Product\Interceptor which is a wrapper for plugins 
                    // to extend the Product
                    
                    // only process simple products as they contain the stock
                    if ($item->getTypeId() != 'simple') {
                        if($this->_logLevel){
                            $this->_logger->debug($this->_logPrefix.'->syncProducts: * item: "'.$item->getId().'" with sku: "'.$item->getSku().'" is not a simple product');
                        }
                        continue;
                    }
                    
                    $softwearKeyAttr = $item->getCustomAttribute($this->_magentoAttributeNameSoftwearKey);
                    $softwearKey='';
                    if($softwearKeyAttr){
                        $softwearKey = $softwearKeyAttr->getValue();
                    }
                    
                    if(!$softwearKey){
                        if($this->_logLevel){
                            $this->_logger->debug($this->_logPrefix.'->syncProducts: * item: "'.$item->getId().'" with sku: "'.$item->getSku().'" has NO '.$this->_magentoAttributeNameSoftwearKey.' attribute');
                        }
                        // maybe in the future see if we can inform shop owner
                        $simpleProductCount++;
                        $simpleErrorCount++;
                        $this->addSyncLogMessage('Warning: product: "'.$item->getId().'" with sku: "'.$item->getSku().'" has NO '.$this->_magentoAttributeNameSoftwearKey.' attribute');
                        continue;
                    }
                    
                    if($this->_logLevel>1){
                        $this->_logger->debug($this->_logPrefix.'->syncProducts: - item: "'.$item->getId().'" with sku: "'.$item->getSku().'" '.$this->_magentoAttributeNameSoftwearKey.': "'.$softwearKey.'" type: "'.$item->getTypeId().'"');
                    }
                    
                    // get current stock: \Magento\CatalogInventory\Api\Data\StockItemInterface;
                    $stockItem=$this->_stockRegistry->getStockItem($item->getId()); // load stock of that product
                    if($stockItem){
                        $quantityCurrent=$stockItem->getQty();
                        // get new stock (from Softwear)
                        if(array_key_exists($softwearKey,$stockLevels)){
                            if(!is_numeric($stockLevels[$softwearKey])){
                                // unexpected format?
                                if($this->_logLevel){
                                    $this->_logger->debug($this->_logPrefix.'->syncProducts: unexpected stock level format from Softwear "'.$stockLevels[$softwearKey].'", skipping product');
                                }
                                $simpleProductCount++;
                                $simpleErrorCount++;
                                $this->addSyncLogMessage('Warning: product: "'.$item->getId().'" with sku: "'.$item->getSku().'" unexpected stock level format from Softwear "'.$stockLevels[$softwearKey].'", skipping product');
                                continue;
                            }
                            $quantityNew=$stockLevels[$softwearKey];
                        }else{
                            // if it was not present it means the stock is 0
                            $quantityNew=0;
                        }
                        if($this->_logLevel>2){
                            $this->_logger->debug($this->_logPrefix.'->syncProducts:   stock "'.$quantityCurrent.'"');
                        }
                    }else{
                        if($this->_logLevel>1){
                           $this->_logger->debug($this->_logPrefix.'->syncProducts:   cannot get stock info');
                        }
                    }
                    
                    // assign stock
                    if($quantityCurrent != $quantityNew){
                      $stockItem->setQty($quantityNew);
                      // only set the in stock level if the new or the old level was 0
                      if($quantityCurrent==0 || $quantityNew==0){
                        $stockItem->setIsInStock($quantityNew>0 ? true : false);
                      }
                      // do not save stock in dryrun mode
                      if(!$this->_dryRun){
                        try {
                            $stockItem->save();
                            $productRepository->save($item);
                        } catch (\Exception $e) {
                            if($this->_logLevel){
                                $this->_logger->debug($this->_logPrefix.'->syncProducts: failed to save product item: "'.$item->getId().'" with sku: "'.$item->getSku().'" Exception: '.$e->getMessage());
                                $this->_logger->debug($this->_logPrefix.'->syncProducts: skipping simple product');
                            }
                            $simpleProductCount++;
                            $simpleErrorCount++;
                            $this->addSyncLogMessage('Warning: failed to save product: "'.$item->getId().'" with sku: "'.$item->getSku().'" (Exception), skipping product');
                            continue;
                        }
                      }
                      if($this->_logLevel){
                          $this->_logger->debug($this->_logPrefix.'->syncProducts:   product "'.$item->getId().'" stock updated to '.$quantityNew);
                      }
                      $stockUpdateCount++;
                    }
                    
                    $simpleProductCount++;
              }// end simple product loop

            }// end configurable product loop
            
            if($this->_logLevel>2){
                $this->_logger->debug($this->_logPrefix.'->syncProducts: finished page '.$currentPage.' with memory: '.memory_get_usage().' / ?');
            }
            $currentPage++;
            
        }// end page loop
        
        // okay, done, get some statistics
        $endTime=time();
        $runTime=$endTime-$startTime;
        
        if($this->_logLevel){
            $this->_logger->debug($this->_logPrefix.'->syncProducts: ran for '.$runTime.' seconds');
            $this->_logger->debug($this->_logPrefix.'->syncProducts: and '.$stockUpdateCount.' products had their stock updated');
        }
        
        // for product / second counts, assume at least one second passed
        if($runTime==0){
            $runTime++;
        }
        
        if($this->_logLevel){
            $this->_logger->debug($this->_logPrefix.'->syncProducts: processed '.$configurableProductCount.' configurable products ('.round($configurableProductCount/$runTime,2).' per second) of which '.$configurableErrorCount.' had errors');
            $this->_logger->debug($this->_logPrefix.'->syncProducts: processed '.$simpleProductCount.' simple products ('.round($simpleProductCount/$runTime,2).' per second) of which '.$simpleErrorCount.' had errors');
        }
        
        $this->finalizeSyncLog($stockUpdateCount, $simpleProductCount, $configurableErrorCount || $simpleErrorCount);
        
        if($runtimeExceeded){
            // ok set our state to finished, but we could not handle all products so next run should continue...
            $this->setSyncState(false, false, $currentPage);
        }else{
            // ok set our state to finished (and next start from beginning)
            $this->setSyncState(false, true, 1);
        }
        
        if($this->_logLevel){
            $this->_logger->debug($this->_logPrefix.'->syncProducts: done');
        }
        return true;
    }
    
    /**
     * Get the current SyncState as an object
     *
     * @return \Web2All\Softwear\Api\Data\SyncStateInterface
     */
    protected function getSyncState()
    {
        if(!$this->_syncState){
            $this->_syncState = $this->_syncStateRepository->getById(1);
        }
        return $this->_syncState;
    }
    
    /**
     * Set the current SyncState
     *
     * @param boolean $isRunning
     * @param boolean $startBeginning
     * @param int $startPage
     */
    protected function setSyncState($isRunning, $startBeginning, $startPage)
    {
        if(!$this->_syncState){
            $this->_syncState = $this->_syncStateRepository->getById(1);
        }
        // update state
        $this->_syncState->setIsRunning($isRunning ? 1 : 0);
        $this->_syncState->setStartBeginning($startBeginning ? 1 : 0);
        $this->_syncState->setStartPage($startPage);
        $this->_syncStateRepository->save($this->_syncState);
    }
    
    /**
     * Create a new SyncLog entry in the database
     *
     */
    protected function createSyncLog()
    {
        $this->_syncLogEntry = $this->_syncLogFactory->create();
        // we must set something, or the record will not be created
        $this->_syncLogEntry->setIsCompleted(0);
        $this->_syncLogRepository->save($this->_syncLogEntry);
    }
    
    /**
     * Add a message to the SyncLog entry in the database
     *
     * @param string $message
     */
    protected function addSyncLogMessage($message)
    {
        $currentMessage=$this->_syncLogEntry->getLogData();
        if($currentMessage){
            $currentMessage.="\n";
        }
        $currentMessage.=$message;
        $this->_syncLogEntry->setLogData($currentMessage);
        $this->_syncLogRepository->save($this->_syncLogEntry);
    }
    
    /**
     * finalize the SyncLog entry in the database (on successful run)
     *
     * @param int $stockUpdateCount  how many products had their stock level updated
     * @param int $productCount  how many products were processed
     * @param boolean $hasErrors  Were any errors encountered
     */
    protected function finalizeSyncLog($stockUpdateCount, $productCount, $hasErrors)
    {
        $this->_syncLogEntry->setIsCompleted(1);
        $this->_syncLogEntry->setHasErrors($hasErrors ? 1 : 0);
        $this->_syncLogEntry->setNumProductsUpdated($stockUpdateCount);
        $this->_syncLogEntry->setNumProductsProcessed($productCount);
        $this->_syncLogRepository->save($this->_syncLogEntry);
    }
}
