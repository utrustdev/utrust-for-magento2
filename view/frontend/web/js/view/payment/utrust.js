define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'utrust',
                component: 'Utrust_Payment/js/view/payment/method-renderer/utrust-method'
            }
        );
        return Component.extend({});
    }
);