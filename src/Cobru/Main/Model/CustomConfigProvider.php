<?php

namespace Cobru\Main\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
class CustomConfigProvider implements ConfigProviderInterface
{

    protected $_scopeConfig;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Api\Data\StoreInterface $store
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->store = $store;

    }



public function getConfig() {
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    $config = [
        'payment' => [
            'cobru' => [
                'title'=> $this->_scopeConfig->getValue('payment/cobru/title', $storeScope),
                'language'=> $this->getLanguage()
            ]
        ]
    ];

    return $config;
}

public function getLanguage(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Framework\Locale\Resolver');

        return $store->getLocale();
    }

}
