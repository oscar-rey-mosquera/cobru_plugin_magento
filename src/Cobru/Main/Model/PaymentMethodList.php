<?php

namespace Cobru\Main\Model;

use CobruSdk\PaymentMethod;
use Magento\Framework\Option\ArrayInterface;


require __DIR__ . '/../vendor/autoload.php';

class PaymentMethodList implements ArrayInterface
{
   public function toOptionArray(){
       $options = [];

       $paymentMethods = PaymentMethod::toArray();

       foreach ($paymentMethods as $paymentMethod) {

           $options[] = [
             'value' => $paymentMethod,
             'label' => str_replace('_', ' ', strtolower($paymentMethod))
           ];
       }

       return $options;
   }
}
