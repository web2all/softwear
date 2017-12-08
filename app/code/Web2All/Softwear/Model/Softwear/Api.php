<?php
/**
 * Copyright © 2016 Web2All B.V.. All rights reserved.
 * See LICENCE.txt for license details.
 */

namespace Web2All\Softwear\Model\Softwear;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Psr\Log\LoggerInterface;

/**
 */
class Api
{
    
    /**
     * XML parser Factory
     *
     * @var \Magento\Framework\Xml\ParserFactory
     */
    protected $_xmlParserFactory;
    
    /**
     * XML parser Factory
     *
     * @var \Magento\Framework\HTTP\ClientFactory
     */
    protected $_httpClientFactory;
    
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
     * shop key is in format XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX
     *
     * @var string
     */
    protected $_softwearShopkey;
    
    /**
     * Base api domain
     *
     * @var string
     */
    protected $_softwearUrl;
    
    /**
     * Loglevel [0-3]
     *
     * @var int
     */
    protected $_logLevel;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Xml\ParserFactory $xmlParserFactory
     * @param \Magento\Framework\HTTP\ClientFactory $httpClientFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Xml\ParserFactory $xmlParserFactory,
        \Magento\Framework\HTTP\ClientFactory $httpClientFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Psr\Log\LoggerInterface $logger) 
    {
        $this->_xmlParserFactory = $xmlParserFactory;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_softwearShopkey=$this->_scopeConfig->getValue('web2all_softwear/general/softwear_shopkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_softwearUrl=$this->_scopeConfig->getValue('web2all_softwear/general/softwear_swapi_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $this->_logLevel=(int)$this->_scopeConfig->getValue('web2all_softwear/loggingerrors/loglevel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($this->_logLevel<0 || $this->_logLevel>3){
            // invalid setting, default to 1
            $this->_logLevel=1;
        }
    }
    
    /**
     * getStock
     * 
     * This method is used to recieve the actual stock of a articles
     * The result is an assoc array with as key the 'Softwear key' which is
     * some kind of article code. The value is the amount of articles in stock.
     * If an article is not returned, its not in stcok.
     * 
     * @throws \InvalidArgumentException
     * @param string[] $skuList  Array of SKU codes
     * @return int[]  assoc array with key the 'Softwear key' and value the amount in stock
     */
    public function getStock($skuList)
    {
        /*
        1.1.15
        This method is used to recieve the actual stock of an article.
        Syntax 1:
          http://testapi.softwear.nl/scripts/foxisapi.dll/sww1.wreq1.mpx?[function=]getstock&[token=]<shopid>[&articlecode=<articlecode>][&barcode=<barcode>][&format=<xml/html>][&switches=<logical|detail|perShop>]
        Syntax 2: 
          http://testapi.softwear.nl/getstock/<shopid>/articlecode__<articlecode>/barcode__<barcode> [/format__<xml/html>][/switches__<logical|detail|perShop>]
        elements in [] are optional
        elements in <> should be replaced by literals
        Example: http://testapi.softwear.nl/scripts/foxisapi.dll/sww1.wreq1.mpx?getstock&token=1660-8920-F99A-11E0-BE50-0800-200C-9A66&articlecode=00000002&format=xml&switches=logical
        parameter: token:string
          UUID shopid
        parameter: articlecode:string
          Articlecode of article for which stock should be returned.
          Articlecode is case-sensitive.
          Articlecode can be either a single artilecode or a comma separated list of articlecodes.
        parameter: barcode:string
          Barcode of article for which stock should be returned.
          Barcode is case-sensitive.
          Barcode can be either a single barcode or a comma separated list of barcodes.
          Can be any barcode of article, stock for all variations of article will be returned.
        parameter: switches:string
          comma separated string of switches.
          - logical returns result as 1 or 0. 1 Means that there is at least one piece of that article in stock,
            regardless of size and color. The logical option can only be used when stock is retrieved for a
            single articlecode.
          - detail returns result per SKU (default)
          - perShop returns results per shop (implies detail)
        */
        // http://testapi.softwear.nl/getstock/CE0A-50CF-B68A-2A4D-8AD7-0E41-CF63-3E5B/articlecode__4714,4717/format__xml/switches__details
        
        $result=array();
        
        // test input and configuration
        if(!is_array($skuList)){
          throw new \InvalidArgumentException('The skuList must be an Array of article SKU codes (string)');
        }
        if(!$this->_softwearShopkey){
          throw new \Exception('No configuration present for web2all_softwear/general/softwear_shopkey');
        }
        if(!$this->_softwearUrl){
          throw new \Exception('No configuration present for web2all_softwear/general/softwear_swapi_url');
        }
        
        // build request
        $requestUrl='http://'.$this->_softwearUrl.'/getstock/'.$this->_softwearShopkey.'/format__xml/switches__details/articlecode__'.urlencode(implode(',',$skuList));
        if($this->_logLevel){
            $this->_logger->debug('\Web2All\Softwear\Model\Softwear\Api->getStock: request url" "'.$requestUrl.'"');
        }
        
        // send request
        $httpClient = $this->_httpClientFactory->create();
        //$httpClient->addHeader('User-Agent','Web2All - Magento - SoftwearSync');
        $httpClient->get($requestUrl);
        if($httpClient->getStatus()!=200){
          throw new \Exception('SWAPI request failed for '.implode(',',$skuList).' with status code: '.$httpClient->getStatus());
        }
        
        // parse response
        $xmlParser = $this->_xmlParserFactory->create();
        
        $xmlParser->loadXML($httpClient->getBody());
        // test result (top level tag)
        // - <error>: contains <message>$errorMessage</message>
        // - <response>: contains <stock variant="$softwearKey">$stockAmount</stock>
        $dom=$xmlParser->getDom();
        
        switch($dom->documentElement->tagName){
          case 'error':
            // get the error message, should be in <message> sub-tag, so lets try to find it
            $messageContentElement=$dom->documentElement;
            foreach($dom->documentElement->getElementsByTagName('message') as $messageElement){
                // ok, found a message tag, use it
                $messageContentElement=$messageElement;
                break;
            }
            throw new \Exception('SWAPI request failed with message: '.$messageContentElement->textContent);
            break;
          
          case 'response':
            foreach($dom->documentElement->childNodes as $childNode){
              if($childNode->nodeName=='stock' && $childNode->hasAttribute('variant')){
                  $result[$childNode->getAttribute('variant')]=$childNode->textContent;
              }
            }
            break;
            
          default:
            // unknown result
            throw new \Exception('SWAPI response has unknown documentElement: '.$dom->documentElement->tagName);
            break;
        }
        
        // return result
        return $result;
    }
}
