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
            this.lootlyWidget.subscribe(function (newValue) {
                var c = that.isCorrectlyRendered();
                if (!c) {
                    var scriptUrl = $('#lootly-widget').data('script-url');
                    scriptUrl += '#renew';
                    $('#lootly-widget').data('script-url', scriptUrl);
                    $('#lootly-widget').empty();
                    lootlyJsInitialized = false;
                    that.initializeLootlyJs();
                }
            });
            window.addEventListener('widgetRewardRedeemed', (event) => {
                var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
            });
        },
        loadJsAfterKoRender: function () {
            that.initializeLootlyJs();
        },
        initializeLootlyJs: function () {
            if (!lootlyJsInitialized) {
                var scriptUrl = $('#lootly-widget').data('script-url');
                require([scriptUrl], function () {
                    lootlyJsInitialized = true;
                });
            }
        },
        isCorrectlyRendered: function () {
            var customerId = this.lootlyWidget().customerId;
            var data = this.lootlyWidget();
            if ($('#lootly-widget').data('customer-id') != customerId) {
                return false;
            } else {
                return true;
            }
        }
    });
});
