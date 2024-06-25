/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'mage/mage',
    'mage/decorate'
], function (Component, customerData, $) {
    'use strict';
    window.ccustomerData = customerData;
    var lootlyJsInitialized = false;
    var that;
    var iterator = 0;
    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.lootlyWidget = customerData.get('lootly-widget');
            that = this;
            window.addEventListener('widgetRewardRedeemed', (event) => {
                var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
            });
        },
        loadJsAfterKoRender:function(){
            that.waitForCorrectRender();
        },
        initializeLootlyJs: function () {
            if (!lootlyJsInitialized){
                var scriptUrl = $('#lootly-widget').data('script-url');
                require([scriptUrl],function(){
                    lootlyJsInitialized = true;
                });
            }
        },
        waitForCorrectRender: function () {
            iterator++;
            var customerId = that.lootlyWidget().customerId;
            var data = that.lootlyWidget();
            if (iterator==10){
                customerData.reload(['lootly-widget'],false);
                that.initializeLootlyJs();
            }
            if ( data['customerId']==''
                || $('#lootly-widget').data('customer-id')!=customerId
                || typeof data['customerId']=='undefined'
            ){
                setTimeout(that.waitForCorrectRender,500);
            } else {
                that.initializeLootlyJs();
            }
        }
    });
});
