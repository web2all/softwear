<?php
namespace Web2All\Softwear\Model\Message;
class SyncDisabled implements \Magento\Framework\Notification\MessageInterface
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
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_urlBuilder = $urlBuilder;
    }
    
    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('Web2All\Softwear\Model\Message\SyncDisabled');
    }
    
    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        $syncEnabled=$this->_scopeConfig->getValue('web2all_softwear/general/enable_sync', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($syncEnabled){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $message = __('The Softwear Sync is disabled.') . ' ';
        $url = $this->_urlBuilder->getUrl('adminhtml/system_config');
        $message .= __('Please go to <a href="%1">Configuration &gt; Web2All Extensions</a> and configure and enable the Softwear sync', $url);
        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->_urlBuilder->getUrl('adminhtml/system_config');
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
