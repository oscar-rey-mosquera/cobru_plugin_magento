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

namespace Cobru\Main\Controller\Payment;
use CobruSdk\CobruRequest;
use CobruSdk\CobruSdk;
use CobruSdk\PaymentMethod;
use Magento\Sales\Model\Order;

require __DIR__ . '/../../vendor/autoload.php';

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $checkoutSession;
    protected $orderFactory;
    protected $cartManagement;
    protected $quote;
    protected $resultRedirect;
    protected $_curl;
    protected $urlInterface;
    protected $storeManager;
    protected $image;

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
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $image
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
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->image = $image;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->validation();

        $result = $this->resultJsonFactory->create();

        $order = $this->orderRepository->get($orderId);

        if(!$order){
             throw new Exception;
        }

        $this->isValidOrder($order);

        $config = $this->getConfig();

        $cobruRequest = new CobruRequest();

        $cobruRequest->callback = $config['callback'];

        $cobruRequest->paymentMethodEnabled = $config['payment_method'];

        $cobruRequest->amount = round($order->getGrandTotal());

        $cobruRequest->description = $this->getStoreName() . ', ' . 'ref '. $order->getIncrementId();

        $cobruRequest->expirationDays = $config['expirations_days'];


         $cobruRequest->images = $this->getUrlImageProducts($order);

      try{
        $cobru = $this->cobru()->auth()->createCobru($cobruRequest);

        $order->setCobruReferenceId($cobru->url);

        $order->save();
          
        return $result->setData([
          'url' => $cobru->getUrlCheckout()  
        ]);

      }catch( \Exception $e) {

        return $result->setData('error');
      }


    }

    public function getStoreName(){

        return $this->storeManager->getStore()->getName();
    }


    public function getUrlImageProducts($order){

        return array_map(function($item){
           return $this->image->init($item->getProduct(), 'product_thumbnail_image')
           ->resize(500)
           ->getUrl();
        }, $order->getAllItems());

    }

    public function validation() {

        $orderId = $_REQUEST['order_id'];

        if(!isset($orderId) && !is_numeric($orderId)){

            throw new Exception;
        }

        return $orderId;

    }


    public function cobru() {
        $config = $this->getConfig();

      return new CobruSdk($config['key'], $config['refresh'], !$config['test']);
    }


    public function isValidOrder($order) {

        if($order->getState() != Order::STATE_NEW){

            throw new Exception('Order invalid');
        }

    }

    public function getConfig() {


        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $callback = $this->scopeConfig->getValue('payment/cobru/callback',$storeScope);

        $key = $this->scopeConfig->getValue('payment/cobru/key',$storeScope);

        $refresh = $this->scopeConfig->getValue('payment/cobru/refresh',$storeScope);

        $expirationDays = $this->scopeConfig->getValue('payment/cobru/expiration_days',$storeScope);

        $paymentMethod = $this->scopeConfig->getValue('payment/cobru/paymentMethod',$storeScope);

        $test = $this->scopeConfig->getValue('payment/cobru/test',$storeScope) == '1' ? true : false;

        if(empty($callback)) {

            $callback =  $this->urlInterface->getUrl('cobru/callback');
        }

        return [
            'callback' => trim($callback),
            'test' => $test,
            'expirations_days' => $expirationDays,
            'payment_method' => explode(',' ,$paymentMethod),
            'refresh' => $refresh,
            'key' => $key
        ];
    }

}
