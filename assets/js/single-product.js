/**
 * Stanray PDP redesign — gallery, variation pills, qty stepper, wishlist placement
 */
(function ($) {
    'use strict';

    $(function () {

        /* ── Gallery: arrows + thumbnails ─────────────────────────────── */
        var $gallery = $('.pdp-gallery');
        if ($gallery.length) {
            $gallery.each(function () {
                var $g       = $(this);
                var $slides  = $g.find('.pdp-gallery__slide');
                var $thumbs  = $g.find('.pdp-gallery__thumb');
                var count    = $slides.length;
                var current  = 0;

                function show(index) {
                    current = (index + count) % count;
                    $slides.removeClass('is-active').eq(current).addClass('is-active');
                    $thumbs.removeClass('is-active').eq(current).addClass('is-active');
                }

                $g.on('click', '.pdp-gallery__arrow--prev', function () { show(current - 1); });
                $g.on('click', '.pdp-gallery__arrow--next', function () { show(current + 1); });
                $g.on('click', '.pdp-gallery__thumb', function () { show($(this).data('index')); });
            });
        }

        /* ── Variation pills → sync hidden <select> ───────────────────── */
        $('.pdp-variation').each(function () {
            var $wrap  = $(this);
            var $pills = $wrap.find('.pdp-pill');
            var $select = $wrap.find('.pdp-variation__select');

            $pills.on('click', function () {
                var $pill = $(this);
                if ($pill.hasClass('is-disabled')) return;
                $pills.removeClass('is-active');
                $pill.addClass('is-active');
                $select.val($pill.data('value')).trigger('change');
                $('.pdp-variation-alert').removeClass('is-visible');
            });

            // Reflect external changes (e.g. "Clear" link) back onto the pills.
            $select.on('change', function () {
                var val = $select.val();
                $pills.removeClass('is-active');
                if (val) {
                    $pills.filter('[data-value="' + val + '"]').addClass('is-active');
                }
            });
        });

        /* Grey out pill options WooCommerce marks unavailable on the hidden <select> */
        $(document.body).on('woocommerce_variation_has_changed check_variations', function () {
            $('.pdp-variation').each(function () {
                var $wrap = $(this);
                $wrap.find('.pdp-variation__select option').each(function () {
                    var $opt = $(this);
                    if (!$opt.val()) return;
                    var $pill = $wrap.find('.pdp-pill[data-value="' + $opt.val() + '"]');
                    $pill.toggleClass('is-disabled', $opt.prop('disabled'));
                });
            });
        });

        /* Quantity +/- buttons are already handled globally by main.js
           (enhanceQuantity()), which decorates every .quantity div — no
           need to duplicate that here. */

        /* ── Move the wishlist button onto the Add to Cart row ─────────── */
        var $wishBtn = $('.entry-summary .wishlist-btn');
        var $atcRow  = $('.pdp-atc-row');
        if ($wishBtn.length && $atcRow.length) {
            $atcRow.append($wishBtn);
        }

        /* Review-form star rating: WooCommerce's own single-product.js already
           progressively-enhances #rating into a <p class="stars"> widget with
           star-1..star-5 links (marking the picked one .active) — it just had
           no CSS since core WC styles are disabled. Styled in woocommerce.css,
           no JS needed here. */

    });

})(jQuery);
