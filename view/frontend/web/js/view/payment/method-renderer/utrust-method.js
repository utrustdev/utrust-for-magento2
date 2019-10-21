/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
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
            return window.checkoutConfig.payment.instructions[this.item.method];
        },
        afterPlaceOrder: function () {
            $.mage.redirect(window.checkoutConfig.payment.utrust.redirectUrl);
        }
    });
});

