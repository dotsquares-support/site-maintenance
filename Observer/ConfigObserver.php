<?php
namespace Dotsquares\Maintenance\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ConfigObserver implements ObserverInterface
{
	
	protected $scopeConfig;
	protected $helperMaintenance;
	protected $resourceConfig;
    /**
     * @param Logger $logger
     */
    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Dotsquares\Maintenance\Helper\Data $helperMaintenance,
		\Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperMaintenance = $helperMaintenance;
        $this->resourceConfig = $resourceConfig;
    }

    public function execute(EventObserver $observer)
    {
		$enabled = $this->helperMaintenance->getConfigValue('maintenance/general/enable');
			if($enabled == '0' || is_null($enabled)){
				$this->resourceConfig->saveConfig('maintenance/general/enable_time',NULL,'default',0);
			}else{
				$currentDatetime = $this->helperMaintenance->getCurrentDatetime();
				$this->resourceConfig->saveConfig('maintenance/general/enable_time',$currentDatetime,'default',0);		
			}
 		return;
    }
}