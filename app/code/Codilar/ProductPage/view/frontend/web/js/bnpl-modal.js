/**
 * Turns a Tabby / Tamara popup into a Magento modal and opens it from [data-bnpl-open="<provider>"].
 * Magento's modal gives us the dark overlay, Esc / outside-click to close, focus trap and page scroll lock.
 */
define(['jquery', 'Magento_Ui/js/modal/modal'], function ($, modal) {
    'use strict';

    return function (config, element) {
        var $popup = $(element);

        modal({
            type: 'popup',
            modalClass: 'bnpl-modal bnpl-modal--' + config.provider,
            innerScroll: true,
            clickableOverlay: true,
            // Focus our own close button on open (Magento's is hidden); Esc only works when focus is inside
            focus: '.bnpl-popup__close',
            buttons: []
        }, $popup);
        $popup.removeAttr('hidden');

        // The whole box is the trigger: click, or Enter / Space when it has keyboard focus
        $(document).on('click keydown', '[data-bnpl-open="' + config.provider + '"]', function (event) {
            if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            event.preventDefault();
            $popup.modal('openModal');
        });

        $popup.on('click', '[data-bnpl-close]', function () {
            $popup.modal('closeModal');
        });
    };
});
