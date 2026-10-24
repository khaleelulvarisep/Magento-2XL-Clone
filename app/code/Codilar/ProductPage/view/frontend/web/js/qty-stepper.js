/**
 * − / + buttons around the product page quantity input.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $root = $(element),
            $input = $root.find('input.qty'),
            min = parseFloat(config.min) || 1;

        $root.on('click', '[data-role=qty-step]', function (event) {
            var step = parseFloat($(this).data('step')),
                value = (parseFloat($input.val()) || min) + step;

            event.preventDefault();
            $input.val(Math.max(min, value)).trigger('change').trigger('keyup');
        });
    };
});
