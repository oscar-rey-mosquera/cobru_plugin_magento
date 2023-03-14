/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        "jquery",
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/authentication-messages',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/place-order',
        'https://checkout.epayco.co/checkout.js'
    ],
    function ($,Component,url,quote,checkoutData,messageContainer, urlBuilder, customer, placeOrderService) {
        'use strict';
        return Component.extend({
            defaults: {
                self:this,
                template: 'Cobru_Main/payment/cobru'
            },
            redirectAfterPlaceOrder: false,
            renderCheckout: async function() {

                const quoteId = quote.getQuoteId()
               
                const orderId = await this.createOrder(quoteId);


                await $.ajax({
                    url: url.build("cobru/payment/index"),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    method: 'POST',
                    async: false,
                    data:  {
                        "order_id": orderId
                    },
                    success: function(data){
                        
                        if(data == 'error') {
                            return
                        }

                        location.replace(data.url)
                    },
                    error :function(error){
                        console.log('error: '+error);
                    }
                });
                
            },
           async createOrder(quoteId) {
              const paymentMethod = quote.paymentMethod()?.method

              let createOrderUrl = customer.isLoggedIn() ? 
                                     urlBuilder.createUrl('/carts/mine/payment-information', {}) :
                                     urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                                        quoteId
                                     })
                const payload = {
                    cartId : quoteId,
                    billingAddress: quote.billingAddress(),
                    paymentMethod : {
                        method : paymentMethod
                    },
                    email : quote.guestEmail
                }

              return await placeOrderService(createOrderUrl, payload, messageContainer)
                 

            },
            getdisplayTitle: function () {
                return window.checkoutConfig.payment.cobru.title;
            },
            text: function(){
                return window.checkoutConfig.payment.cobru.text;
            },
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            responseAction: function(){
                return window.checkoutConfig.payment.cobru.responseAction;
            },
        });
    }
);
