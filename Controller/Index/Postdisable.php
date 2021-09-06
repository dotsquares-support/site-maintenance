<?php
 
namespace Dotsquares\Maintenance\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Controller\Result\JsonFactory;
use Dotsquares\Maintenance\Helper\Data as HelperMaintenance;
 
class Postdisable extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    
	protected $resourceConfig;
	
	protected $cacheTypeList;
	
	protected $cacheFrontendPool;
	
	protected $helperMaintenance;
	
	protected $resultJsonFactory;
	
    public function __construct(
			Context $context, 
			PageFactory $resultPageFactory, 
			Config $resourceConfig,
			TypeListInterface $cacheTypeList,
			Pool $cacheFrontendPool,
			HelperMaintenance $helperMaintenance,
			JsonFactory $resultJsonFactory
	){
        parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
        $this->resourceConfig = $resourceConfig;   
        $this->cacheTypeList = $cacheTypeList;    
        $this->cacheFrontendPool = $cacheFrontendPool;
		$this->helperMaintenance = $helperMaintenance;
		$this->resultJsonFactory = $resultJsonFactory; 
    }
 
    public function execute()
    {
	   $result = array();
	   $result["result"]="failed";
	   $resultJson = $this->resultJsonFactory->create();
	   $resultJson->setHeader('Content-type', 'application/json', true);
	   $resultJson->setData($result);
	   if ($this->getRequest()->getQuery('isAjax')) {
			if (!($formKey = $this->getRequest()->getParam('form_key', null))) { // TODO: use of form kay validation, it's bypassed for now
				$resultJson->setData($result);
				return $resultJson;
			}
			$isAjax = $this->getRequest()->getParam('isAjax', false);
			if($isAjax == false){
				$resultJson->setData($result);
				return $resultJson;
			}
			if(!$this->helperMaintenance->isNeedEnable()){
				$this->resourceConfig->saveConfig('maintenance/general/enable',0,'default',0);
				$this->resourceConfig->saveConfig('maintenance/general/enable_time',NULL,'default',0);
				$types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
				foreach ($types as $type) {
					$this->cacheTypeList->cleanType($type);
				}
				foreach ($this->cacheFrontendPool as $cacheFrontend) {
					$cacheFrontend->getBackend()->clean();
				}
				$result["result"]="success";
			}
	  }
		$resultJson->setData($result);
		return $resultJson;
	 
    }
}