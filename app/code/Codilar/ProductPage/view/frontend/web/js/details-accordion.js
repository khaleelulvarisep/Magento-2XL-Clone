/**
 * Product details accordion; "Be the first to review" opens the Reviews item and scrolls to it.
 */
define(['jquery', 'mage/accordion'], function ($) {
    'use strict';

    return function (config, element) {
        var $accordion = $(element);

        $accordion.accordion({
            openedState: 'active',
            collapsible: true,
            multipleCollapsible: true,
            active: [0],
            animate: 200
        });

        $('.product-info-main').on('click', '.reviews-actions a', function () {
            var $items = $accordion.find('[data-role=collapsible]'),
                index = $items.index($('#tab-label-reviews'));

            if (index !== -1) {
                $accordion.accordion('activate', index);
                $('html, body').stop().animate({
                    scrollTop: $items.eq(index).offset().top - 50
                }, 300);
            }
        });
    };
});
