<?php
namespace Web2All\Softwear\Model\Message;
class HasSyncErrors implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    
    /**
     * SyncLogRepository implementing class
     * 
     * implemented in \Web2All\Softwear\Model\SyncLogRepository 
     * 
     * @var \Web2All\Softwear\Api\SyncLogRepositoryInterface
     */
    protected $_syncLogRepository;
    
    /**
     * Builder to create search filters
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;
    
    /**
     * Builder to create sort order for SearchCriteriaBuilder
     *
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    protected $_sortOrderBuilder;
    
    /**
     * Last SyncLog object
     *
     * @var \Web2All\Softwear\Model\SyncLog
     */
    protected $_lastSyncLogEntry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Web2All\Softwear\Api\SyncLogRepositoryInterface $syncLogRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Web2All\Softwear\Api\SyncLogRepositoryInterface $syncLogRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_urlBuilder = $urlBuilder;
        $this->_syncLogRepository = $syncLogRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        
        $this->_lastSyncLogEntry=null;
    }
    
    /**
     * Load the last SyncLog entry in from database
     *
     */
    protected function loadLastSyncLog()
    {
        if($this->_lastSyncLogEntry){
          return;
        }
        $this->_searchCriteriaBuilder->setCurrentPage(1);
        $this->_searchCriteriaBuilder->setPageSize(1);
        $this->_searchCriteriaBuilder->addFilter('is_completed', 1);
        $this->_sortOrderBuilder->setField('start_time');
        $this->_sortOrderBuilder->setDescendingDirection();
        $sortOrder=$this->_sortOrderBuilder->create();
        $this->_searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $entries=$this->_syncLogRepository->getList($searchCriteria);
        foreach($entries->getItems() as $entry){
            $this->_lastSyncLogEntry = $entry;
            break;
        }
    }
    
    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('Web2All\Softwear\Model\Message\HasSyncErrors');
    }
    
    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // only show when sync is enabled and last run had errors
        $this->loadLastSyncLog();
        $syncEnabled=$this->_scopeConfig->getValue('web2all_softwear/general/enable_sync', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($syncEnabled){
            if($this->_lastSyncLogEntry){
              if($this->_lastSyncLogEntry->getHasErrors()){
                return true;
              }
            }
            return false;
        }else{
            return false;
        }
    }
    
    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $this->loadLastSyncLog();
        $message = __('The Softwear Sync had errors. ');
        $url = $this->_urlBuilder->getUrl('catalog/product');
        $message .= __('Please go to <a href="%1">Products &gt; Catalog</a> and correct the errors. <br \>', $url);
        if($this->_lastSyncLogEntry){
            $message .= __('Errors:  <br \>');
            $message .= __($this->_lastSyncLogEntry->getLogData());
        }
        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->_urlBuilder->getUrl('catalog/product');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        // if you return self::SEVERITY_MAJOR the sticky box on top will be empty
        // if you return self::SEVERITY_CRITICAL the sticky box will always show your text
        // other severities: SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_MAJOR;
    }
}
