<?php
/**
 * Module for payment provide by ePayco
 * Copyright (C) 2017
 *
 * This file is part of EPayco/EPaycoPayment.
 *
 * EPayco/EPaycoPayment is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Cobru\Main\Controller\Callback;
use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use CobruSdk\CobruSdk;

require __DIR__ . '/../../vendor/autoload.php';

class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $checkoutSession;
    protected $orderFactory;
    protected $cartManagement;
    protected $quote;
    protected $resultRedirect;
    protected $_curl;
    protected $orderCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Helper\Context $contextApp,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->cartManagement = $cartManagement;
        $this->quote = $quote;
        $this->_curl = $curl;
        $this->contextApp = $contextApp;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }


public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
{
  return null;
}

public function validateForCsrf(RequestInterface $request): ?bool
{
     return true;
}

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {        

            if(!array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
                throw new \Exception('error');
            }
    
    
            if (!$this->cobru()->isCobru($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                throw new \Exception('error');
            }
            
            $data = file_get_contents('php://input');
    
            if(!$data) {
                throw new \Exception('error');
            }

            $data = json_decode($data, true);

    
            if($data['state'] != 3){
               
                throw new \Exception('error');
            }
    
    
            $order = $this->finOrderByCobruReferenceId($data['url']);
    
              
            if($order->getState() != Order::STATE_NEW){
    
                throw new Exception('Order invalid');
            }
    
    
            $order->setState(Order::STATE_PROCESSING, true);
            $order->setStatus(Order::STATE_PROCESSING, true);
            
            $order->save();
    
       
    }


    public function finOrderByCobruReferenceId($cobruId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

         $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
          
         $connection = $resource->getConnection();
           
          $sql = "SELECT * FROM sales_order WHERE cobru_reference_id = '$cobruId'";
           
          $result = $connection->fetchAll($sql);

          if(!$result){ 
            throw new \Exception('error');
          }

          return $this->orderRepository->get($result[0]['entity_id']);
    }



    public function cobru() {

      return new CobruSdk('', '', false);
    }

}