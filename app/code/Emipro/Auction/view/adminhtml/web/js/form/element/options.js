define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        /**
         * Init
         */
        initialize: function () {
            this._super();

            this.fieldDepend(this.value());

            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.fieldDepend(value);

            return this._super();
        },
        /**
         * Update field dependency
         *
         * @param {String} value
         */
        fieldDepend: function (value) {
            setTimeout(function () {
                var field1 = uiRegistry.get('index = auto_extend_time');
                if (field1.visibleValue == value) {
                    field1.show();
                } else {
                    field1.hide();
                }
                var field2 = uiRegistry.get('index = auto_extend_time_left');
                if (field2.visibleValue == value) {
                    field2.show();
                } else {
                    field2.hide();
                }
            }, 1);
            return this;
        }
    });
});