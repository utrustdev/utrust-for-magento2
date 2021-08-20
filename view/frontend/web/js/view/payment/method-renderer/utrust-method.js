define([
    'jquery',
    'Magento_Checkout/js/view/payment/default'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Utrust_Payment/payment/utrust',
            redirectAfterPlaceOrder: false
        },

        getInstructions: function () {
            return window.checkoutConfig.payment.utrust.instructions;
        },

        afterPlaceOrder: function () {
            $.mage.redirect(window.checkoutConfig.payment.utrust.redirectUrl);
            return false;
        }, 
        
        redirectUtrust: function () {
            if(window.checkoutConfig.payment.utrust.flow == 1){
                $.mage.redirect(window.checkoutConfig.payment.utrust.redirectUrl);
            }else{
                this.placeOrder();
            }
        },

        getLogoSrc: function () {
            return window.checkoutConfig.payment.utrust.logoUrl;
        }, 
    });
});
