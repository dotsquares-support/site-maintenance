<?php
namespace Dotsquares\Maintenance\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface as Logger;

class InitControllerRouters implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $logger;
	
	protected $scopeConfig;
	
	protected $appState;
	protected $request;
	protected $_response;
	protected $_layoutFactory;
	protected $helperMaintenance;


    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\State $appState,
		 \Magento\Framework\App\Request\Http $request,
		\Magento\Framework\App\Response\Http $response,
		\Magento\Framework\View\LayoutFactory $layoutFactory,
		\Dotsquares\Maintenance\Helper\Data $helperMaintenance
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
		$this->request = $request;
		$this->_response = $response;
		$this->_layoutFactory = $layoutFactory;
		$this->helperMaintenance = $helperMaintenance;
    }

    public function execute(EventObserver $observer)
    {
       $areaCode = $this->appState->getAreaCode();
       $action     = $this->request->getActionName();

	   if(($areaCode != \Magento\Framework\App\Area::AREA_ADMINHTML) && ($action != 'postDisable'))
	   {
			$isEnabled = $this->helperMaintenance->getConfigValue('maintenance/general/enable');
			if ($isEnabled == 1) 
			{
				$allowedIPs = $this->helperMaintenance->getConfigValue('maintenance/general/whitelist');
				$allowedIPs = preg_replace('/ /', '', $allowedIPs);
				$IPs = array();
				if ('' !== trim($allowedIPs)) 
				{
					$IPs = explode(',', $allowedIPs);
				}
				$currentIP = $_SERVER['REMOTE_ADDR'];

				if (!in_array($currentIP, $IPs))
				{
					$this->logger->debug('Access denied  for IP: ' . $currentIP);
					
					$layout = $this->_layoutFactory->create(); 
					$html = $layout->createBlock("Magento\Framework\View\Element\Template")->setTemplate("Dotsquares_Maintenance::maintenancemode/maintenancemode.phtml")->toHtml();
					if ($html !== '') 
					{ 
						$this->_response->setHeader('Retry-After', '5000');
						$this->_response->setHeader('Status', '503 Service Temporarily Unavailable');
						//$this->_response->setHeader($protocol, '503 Service Temporarily Unavailable', true, 503);
						$this->_response->setBody($html);
						$this->_response->sendResponse();
						return true;  
					}
				} else {
					$this->logger->debug('Access granted for admin with IP: ' . $currentIP);
				}
		   } //end enabled if
	   }
	   return; 
    }
}