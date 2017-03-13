'use strict';

/**
 * Activated locales fetcher
 *
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/job/common/edit/field/select'
], function (
    $,
    _,
    __,
    FetcherRegistry,
    SelectField
) {
    return SelectField.extend({
        /**
         * {@inherit}
         */
        configure: function () {
            return $.when(
                FetcherRegistry.getFetcher('locale').fetchActivated(),
                SelectField.prototype.configure.apply(this, arguments)
            ).then(function (activatedLocalesList) {
                if (_.isEmpty(activatedLocalesList)) {
                    this.config.readOnly = true;
                    this.config.options = {'NO OPTION': __('pim_enhanced_connector.family_processor.locale.no_locale')};
                } else {
                    var codes = {};

                    _.each(activatedLocalesList, function (locale, index) {
                        codes[locale.code] = locale.code;
                    });

                    this.config.options = codes;
                }
            }.bind(this));
        }
    });
});
