<?php
namespace Dotsquares\Maintenance\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $scopeConfig;
	
	protected $storeManager;
	
	protected $timezoneInterface;
	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        parent::__construct($context);
		$this->scopeConfig = $context->getScopeConfig();
		$this->storeManager = $storeManager;
		$this->timezoneInterface = $timezoneInterface;
    }
	
		public function isNeedEnable(){
			$total_hour = $this->getConfigValue('maintenance/countdown/total_hours');
			//echo "</br>";
			$total_min = $this->getConfigValue('maintenance/countdown/total_min');
			//echo "</br>";
			$enabledDateTime = $this->getConfigValue('maintenance/general/enable_time');
			//echo "</br>";
			$givenDate = strtotime($enabledDateTime);	
	 		//echo "</br>";
			$endDateTime = date('Y/m/d H:i:s', strtotime("+$total_hour hours +$total_min minutes", $givenDate));
			//echo "</br>";
			$currentDatetime = $this->getCurrentDatetime(); 	
			
			if(strtotime($currentDatetime) < strtotime($endDateTime)){
				return true;
			}
			return false;
		}  

		public function getConfigValue($path=null){
			$storeId = $this->storeManager->getStore()->getId();
			 return $this->scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
		}
		
		public function getCurrentDatetime(){
			return $this->timezoneInterface->date()->format('Y/m/d H:i:s');
		}
		
}