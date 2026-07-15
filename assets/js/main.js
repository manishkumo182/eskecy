/**
 * StanRay Custom Theme — main.js
 * Header scroll, mobile menu, mini cart drawer, search toggle
 */

(function($) {
    'use strict';

    /* ── HEADER SCROLL EFFECT ──────────────────────────────────── */
    const header = document.getElementById('site-header');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 20);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ── SEARCH TOGGLE ──────────────────────────────────────────── */
    const searchToggle = document.querySelector('.header-search-toggle');
    const searchBar    = document.getElementById('header-search');
    const searchField  = searchBar ? searchBar.querySelector('.search-field') : null;

    if (searchToggle && searchBar) {
        const closeSearch = (returnFocus) => {
            searchBar.setAttribute('hidden', '');
            searchToggle.setAttribute('aria-expanded', 'false');
            if (returnFocus) searchToggle.focus();
        };
        const openSearch = () => {
            searchBar.removeAttribute('hidden');
            searchToggle.setAttribute('aria-expanded', 'true');
            if (searchField) searchField.focus();
        };

        searchToggle.addEventListener('click', () => {
            const isOpen = searchBar.hasAttribute('hidden') === false;
            if (isOpen) {
                closeSearch(false);
            } else {
                openSearch();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !searchBar.hasAttribute('hidden')) {
                closeSearch(true);
            }
        });

        // Close when clicking outside the search bar / toggle
        document.addEventListener('click', (e) => {
            if (searchBar.hasAttribute('hidden')) return;
            if (searchBar.contains(e.target) || searchToggle.contains(e.target)) return;
            closeSearch(false);
        });
    }

    /* ── MOBILE MENU ─────────────────────────────────────────────── */
    const mobileToggle  = document.querySelector('.mobile-menu-toggle');
    const mobileMenu    = document.getElementById('mobile-menu');
    const mobileClose   = document.querySelector('.mobile-menu__close');
    const mobileOverlay = document.querySelector('.mobile-menu__overlay');

    function openMobileMenu() {
        mobileMenu.classList.add('is-open');
        mobileMenu.setAttribute('aria-hidden', 'false');
        mobileToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        mobileMenu.classList.remove('is-open');
        mobileMenu.setAttribute('aria-hidden', 'true');
        mobileToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openMobileMenu);
    if (mobileClose)  mobileClose.addEventListener('click', closeMobileMenu);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileMenu);

    /* ── MINI CART DRAWER ────────────────────────────────────────── */
    const cartIcon    = document.querySelector('.cart-icon');
    const miniCart    = document.getElementById('mini-cart');
    const cartClose   = document.querySelector('.mini-cart__close');
    const cartOverlay = document.querySelector('.mini-cart__overlay');

    function openMiniCart() {
        if (!miniCart) return;
        miniCart.classList.add('is-open');
        miniCart.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeMiniCart() {
        if (!miniCart) return;
        miniCart.classList.remove('is-open');
        miniCart.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (cartIcon) {
        cartIcon.addEventListener('click', (e) => {
            e.preventDefault();
            openMiniCart();
        });
    }
    if (cartClose)   cartClose.addEventListener('click', closeMiniCart);
    if (cartOverlay) cartOverlay.addEventListener('click', closeMiniCart);

    // Escape key closes all drawers
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
            closeMiniCart();
        }
    });

    /* ── PRODUCT IMAGE HOVER ─────────────────────────────────────── */
    // Pure CSS handles the hover swap — JS not needed here.
    // But we do add loaded class once images are ready.
    document.querySelectorAll('.product-card__img').forEach(img => {
        if (img.complete) {
            img.closest('.product-card__image-wrap')?.classList.add('img-loaded');
        } else {
            img.addEventListener('load', () => {
                img.closest('.product-card__image-wrap')?.classList.add('img-loaded');
            });
        }
    });

    /* ── AJAX ADD TO CART (product grid) ─────────────────────────── */
    // WooCommerce handles this natively with the wc-add-to-cart class.
    // We refresh the mini cart content after add.
    $(document.body).on('added_to_cart', function(e, fragments, cart_hash, button) {
        // Update cart count from fragments
        if (fragments && fragments['.cart-icon__count']) {
            document.querySelectorAll('.cart-icon__count').forEach(el => {
                el.outerHTML = fragments['.cart-icon__count'];
            });
        }
        // Open mini cart on add
        openMiniCart();
        stanrayFlushNotices();
    });

    /* ── PRODUCT GALLERY: keyboard nav ──────────────────────────── */
    // FlexSlider (bundled with WC) handles this natively.

    /* ── STICKY PRODUCT SUMMARY ──────────────────────────────────── */
    // Applied via CSS: position: sticky on .single-product-layout .woocommerce-product-gallery

    /* ── SCROLL REVEAL (lightweight, no library) ─────────────────── */
    if ('IntersectionObserver' in window) {
        const revealEls = document.querySelectorAll('.product-card, .collection-card, .section__title, .editorial__content-col');
        const revealObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

        revealEls.forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(18px)';
            el.style.transition = `opacity 0.55s ease ${i * 0.04}s, transform 0.55s ease ${i * 0.04}s`;
            revealObs.observe(el);
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.is-visible').forEach(el => {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
        });

        // Patch: also trigger via CSS class
        const style = document.createElement('style');
        style.textContent = '.is-visible { opacity: 1 !important; transform: none !important; }';
        document.head.appendChild(style);
    }

    /* ── QUANTITY STEPPER ────────────────────────────────────────── */
    // WooCommerce default quantity input — add +/- buttons
    function enhanceQuantity() {
        document.querySelectorAll('.quantity').forEach(wrap => {
            if (wrap.querySelector('.qty-btn')) return; // already done
            const input = wrap.querySelector('input[type="number"]');
            if (!input) return;

            const minus = document.createElement('button');
            minus.className = 'qty-btn qty-btn--minus';
            minus.type = 'button';
            minus.textContent = '−';
            minus.setAttribute('aria-label', 'Decrease quantity');

            const plus = document.createElement('button');
            plus.className = 'qty-btn qty-btn--plus';
            plus.type = 'button';
            plus.textContent = '+';
            plus.setAttribute('aria-label', 'Increase quantity');

            minus.addEventListener('click', () => {
                const val = parseInt(input.value) || 1;
                const min = parseInt(input.min) || 1;
                if (val > min) { input.value = val - 1; input.dispatchEvent(new Event('change')); }
            });
            plus.addEventListener('click', () => {
                const val = parseInt(input.value) || 1;
                const max = parseInt(input.max) || 999;
                if (val < max) { input.value = val + 1; input.dispatchEvent(new Event('change')); }
            });

            wrap.insertBefore(minus, input);
            wrap.appendChild(plus);
        });
    }

    document.addEventListener('DOMContentLoaded', enhanceQuantity);
    $(document.body).on('wc_fragments_refreshed updated_cart_totals', enhanceQuantity);

})(jQuery);

/* ── TOAST NOTICES ──────────────────────────────────────────────
   Cart AJAX actions (add / remove / qty change) queue WooCommerce
   notices in the session but there's no page reload to display them.
   Flush + render them as toasts immediately instead of letting them
   pile up until the next full page load (e.g. My Account). */
function stanrayFlushNotices() {
    var ajaxUrl = (window.stanrayData && stanrayData.ajaxUrl) || '';
    var nonce   = (window.stanrayData && stanrayData.nonce) || '';
    var $wrap   = jQuery('#stanray-toast');
    if (!ajaxUrl || !$wrap.length) return;

    jQuery.get(ajaxUrl, { action: 'stanray_flush_notices', nonce: nonce }, function(response) {
        if (!response || !response.success || !response.data || !response.data.html) return;

        var $toasts = jQuery(jQuery.parseHTML(response.data.html))
            .filter('.woocommerce-message, .woocommerce-error, .woocommerce-info');

        $toasts.each(function() {
            var $toast = jQuery(this);
            $toast.append('<button type="button" class="stanray-toast__close" aria-label="Dismiss">&times;</button>');
            $wrap.append($toast);

            requestAnimationFrame(function() { $toast.addClass('is-visible'); });

            var dismiss = function() {
                $toast.removeClass('is-visible').addClass('is-leaving');
                setTimeout(function() { $toast.remove(); }, 250);
            };
            $toast.on('click', '.stanray-toast__close', dismiss);
            setTimeout(dismiss, 5000);
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    if (!document.querySelector(".collections-slider")) return;
    new Swiper(".collections-slider", {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true,

        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },

        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },

        breakpoints: {
            320: {
                slidesPerView: 1.2,
            },
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });
});


// cart
jQuery(document).ready(function ($) {

    function updateCart(cartKey, qty) {
        $.ajax({
            url: (window.stanrayData && stanrayData.ajaxUrl) || '',
            type: "POST",
            data: {
                action: "update_cart_qty",
                cart_key: cartKey,
                qty: qty,
                nonce: (window.stanrayData && stanrayData.nonce) || ''
            },
            success: function (response) {
                $(document.body).trigger("wc_fragment_refresh");
                stanrayFlushNotices();
            }
        });
    }

    // PLUS
    $(document).on("click", ".qty-plus", function () {
        let parent = $(this).closest(".cart-item");
        let input = parent.find(".qty-input");

        let qty = parseInt(input.val()) + 1;
        input.val(qty);

        updateCart(parent.data("key"), qty);
    });

    // MINUS
    $(document).on("click", ".qty-minus", function () {
        let parent = $(this).closest(".cart-item");
        let input = parent.find(".qty-input");

        let qty = parseInt(input.val()) - 1;

        if (qty < 1) return;

        input.val(qty);

        updateCart(parent.data("key"), qty);
    });

});

jQuery(document).on('click', '.remove-item', function(e) {
    e.preventDefault();

    let url = jQuery(this).attr('href');

    jQuery.get(url, function() {
        jQuery(document.body).trigger('wc_fragment_refresh');
        stanrayFlushNotices();
    });
});

// ── IMPROVEMENT: Announcement bar dismiss ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var bar = document.querySelector('.announcement-bar');
    if (!bar) return;

    // Add dismiss button
    var btn = document.createElement('button');
    btn.innerHTML = '&times;';
    btn.setAttribute('aria-label', 'Dismiss announcement');
    btn.style.cssText = 'position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:inherit;font-size:1.1rem;cursor:pointer;opacity:0.7;line-height:1;';
    bar.style.position = 'relative';
    bar.appendChild(btn);

    btn.addEventListener('click', function() {
        bar.style.transition = 'max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease';
        bar.style.maxHeight = bar.offsetHeight + 'px';
        bar.style.overflow = 'hidden';
        requestAnimationFrame(function() {
            bar.style.maxHeight = '0';
            bar.style.opacity = '0';
            bar.style.padding = '0';
        });
        setTimeout(function() { bar.style.display = 'none'; }, 320);
        sessionStorage.setItem('eskecy_bar_dismissed', '1');
    });

    // Respect session dismissal
    if (sessionStorage.getItem('eskecy_bar_dismissed')) {
        bar.style.display = 'none';
    }
});

// ── IMPROVEMENT: Sticky header active nav highlight ───────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var currentPath = window.location.pathname;
    var navLinks = document.querySelectorAll('.nav-list a, .mobile-nav-list a');
    navLinks.forEach(function(link) {
        if (link.getAttribute('href') && link.getAttribute('href').includes(currentPath) && currentPath !== '/') {
            link.closest('li') && link.closest('li').classList.add('current-menu-item');
        }
    });
});


// ── SIZE GUIDE MODAL ─────────────────────────────────────────────────────────
(function() {
    var modalHTML = '<div class="size-guide-modal" id="size-guide-modal" role="dialog" aria-modal="true" aria-label="Size Guide">'
        + '<div class="size-guide-modal__overlay" id="size-guide-overlay"></div>'
        + '<div class="size-guide-modal__box">'
        + '<button class="size-guide-modal__close" id="size-guide-close" aria-label="Close">&times;</button>'
        + '<h2>Size Guide</h2>'
        + '<p class="sg-subtitle">All measurements in centimetres</p>'
        + '<table class="size-guide-table">'
        + '<thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th><th>Length</th></tr></thead>'
        + '<tbody>'
        + '<tr><td><strong>S</strong></td><td>86–91</td><td>71–76</td><td>91–96</td><td>68</td></tr>'
        + '<tr><td><strong>M</strong></td><td>91–97</td><td>76–81</td><td>96–102</td><td>71</td></tr>'
        + '<tr><td><strong>L</strong></td><td>97–102</td><td>81–86</td><td>102–107</td><td>74</td></tr>'
        + '<tr><td><strong>XL</strong></td><td>107–112</td><td>91–97</td><td>112–117</td><td>77</td></tr>'
        + '</tbody></table>'
        + '<p class="size-guide-tip">📏 Measure around the fullest part of your chest and natural waist. When between sizes, size up for a relaxed streetwear fit.</p>'
        + '</div></div>';

    document.addEventListener('DOMContentLoaded', function() {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        var modal   = document.getElementById('size-guide-modal');
        var overlay = document.getElementById('size-guide-overlay');
        var closeBtn = document.getElementById('size-guide-close');

        function openModal()  { modal.classList.add('is-open');    document.body.style.overflow = 'hidden'; }
        function closeModal() { modal.classList.remove('is-open'); document.body.style.overflow = ''; }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.size-guide-trigger')) { e.preventDefault(); openModal(); }
        });
        if (overlay)  overlay.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    });
})();

// ── POST-PURCHASE REVIEW MODAL ────────────────────────────────────────────────
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('review-popup-modal');
        if (!modal) return;

        var overlay  = document.getElementById('review-popup-overlay');
        var closeBtn = document.getElementById('review-popup-close');
        var skipBtn  = document.getElementById('review-popup-skip');
        var form     = document.getElementById('review-popup-form');
        var stars    = modal.querySelectorAll('.review-popup-star');
        var starsWrap = modal.querySelector('.review-popup-modal__stars');
        var errorEl  = document.getElementById('review-popup-error');
        var rating   = 0;
        var dismissed = false;

        document.body.style.overflow = 'hidden';

        function closeModal() {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            setTimeout(function() { if (modal.parentNode) modal.parentNode.removeChild(modal); }, 200);
        }

        function setRating(value) {
            rating = value;
            starsWrap.setAttribute('data-rating', value);
            stars.forEach(function(star) {
                star.classList.toggle('is-active', parseInt(star.getAttribute('data-value'), 10) <= value);
            });
        }

        stars.forEach(function(star) {
            star.addEventListener('click', function() {
                setRating(parseInt(star.getAttribute('data-value'), 10));
            });
        });

        function dismiss() {
            if (dismissed) { closeModal(); return; }
            dismissed = true;
            var formData = new FormData();
            formData.append('action', 'stanray_dismiss_post_purchase_review');
            formData.append('nonce', stanrayData.nonce);
            fetch(stanrayData.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
            closeModal();
        }

        if (overlay)  overlay.addEventListener('click', dismiss);
        if (closeBtn) closeBtn.addEventListener('click', dismiss);
        if (skipBtn)  skipBtn.addEventListener('click', dismiss);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) dismiss();
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            errorEl.hidden = true;

            if (!rating) {
                errorEl.textContent = 'Please choose a star rating.';
                errorEl.hidden = false;
                return;
            }

            var submitBtn = form.querySelector('.review-popup-modal__submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting…';

            var formData = new FormData(form);
            formData.append('action', 'stanray_submit_post_purchase_review');
            formData.append('nonce', stanrayData.nonce);
            formData.append('rating', rating);

            fetch(stanrayData.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    if (res.success) {
                        dismissed = true;
                        modal.querySelector('.review-popup-modal__box').innerHTML =
                            '<p class="review-popup-modal__thanks">' + res.data.message + '</p>';
                        setTimeout(closeModal, 1800);
                    } else {
                        errorEl.textContent = (res.data && res.data.message) || 'Something went wrong. Please try again.';
                        errorEl.hidden = false;
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Review';
                    }
                })
                .catch(function() {
                    errorEl.textContent = 'Something went wrong. Please try again.';
                    errorEl.hidden = false;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
                });
        });
    });
})();

// ── OVO SHOP: AJAX PAGINATION + SORT & FILTER DRAWER ─────────────────────────
(function () {
    var shop = document.getElementById('ovo-shop');
    if (!shop) return;

    var grid       = document.getElementById('ovo-grid');
    var pagination = document.getElementById('ovo-pagination');
    var loading    = document.getElementById('ovo-loading');

    var ajaxUrl = shop.dataset.ajaxUrl;
    var nonce   = shop.dataset.nonce;
    var shopUrl = shop.dataset.shopUrl;

    var isFetching = false;

    // Current filter/sort state — pagination clicks reuse whatever is active here.
    var state = {
        categories: shop.dataset.currentCat ? [ shop.dataset.currentCat ] : [],
        sort: 'menu_order',
        minPrice: null,
        maxPrice: null,
        page: 1
    };

    function setLoading(on) {
        if (!loading) return;
        if (on) {
            loading.style.display = 'block';
            if (grid)       grid.style.opacity       = '0';
            if (pagination) pagination.style.opacity  = '0';
        } else {
            loading.style.display = 'none';
            if (grid)       grid.style.opacity       = '1';
            if (pagination) pagination.style.opacity  = '1';
        }
    }

    var EMPTY_MSG = '<li style="grid-column:1/-1;text-align:center;padding:4rem 1rem;font-size:0.8rem;color:#aaa;letter-spacing:0.06em;">No products found in this collection.</li>';

    function fetchProducts(pushState) {
        if (isFetching) return;
        isFetching = true;
        setLoading(true);

        var formData = new FormData();
        formData.append('action',  'stanray_filter_products');
        formData.append('nonce',   nonce);
        formData.append('orderby', state.sort);
        formData.append('paged',   state.page);
        state.categories.forEach(function (slug) { formData.append('categories[]', slug); });
        if (state.minPrice !== null) formData.append('min_price', state.minPrice);
        if (state.maxPrice !== null) formData.append('max_price', state.maxPrice);

        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                var d = data.data;

                if (grid) grid.innerHTML = d.html_grid || EMPTY_MSG;
                if (pagination) pagination.innerHTML = d.pagination || '';

                if (pushState) {
                    var newUrl = shopUrl;
                    if (state.page > 1) newUrl = newUrl.replace(/\/?$/, '/page/' + state.page + '/');
                    history.pushState({
                        categories: state.categories, sort: state.sort,
                        minPrice: state.minPrice, maxPrice: state.maxPrice, page: state.page
                    }, '', newUrl);
                }

                if (grid) {
                    var offset = grid.getBoundingClientRect().top + window.scrollY - 20;
                    window.scrollTo({ top: offset, behavior: 'smooth' });
                }
            })
            .catch(function () {})
            .finally(function () {
                isFetching = false;
                setLoading(false);
            });
    }

    // ── Pagination clicks ─────────────────────────────────────
    document.addEventListener('click', function (e) {
        var pageLink = e.target.closest('#ovo-pagination .page-numbers a');
        if (!pageLink) return;
        e.preventDefault();
        var match = (pageLink.href || '').match(/\/page\/(\d+)/);
        state.page = match ? parseInt(match[1]) : 1;
        fetchProducts(true);
    });

    // ── Browser back / forward ────────────────────────────────
    window.addEventListener('popstate', function (e) {
        var s = e.state;
        if (!s) return;
        state.categories = s.categories || [];
        state.sort       = s.sort || 'menu_order';
        state.minPrice   = s.minPrice != null ? s.minPrice : null;
        state.maxPrice   = s.maxPrice != null ? s.maxPrice : null;
        state.page       = s.page || 1;
        fetchProducts(false);
    });

    // ── Sort & Filter drawer ────────────────────────────────────────────
    var trigger  = document.getElementById('ovo-filter-trigger');
    var overlay  = document.getElementById('ovo-filter-overlay');
    var drawer   = document.getElementById('ovo-filter-drawer');
    var closeBtn = document.getElementById('ovo-filter-close');
    var applyBtn = document.getElementById('ovo-filter-apply');
    var clearBtn = document.getElementById('ovo-filter-clear');
    var minInput = document.getElementById('ovo-price-min');
    var maxInput = document.getElementById('ovo-price-max');

    function openDrawer() {
        if (!drawer || !overlay) return;
        overlay.hidden = false;
        requestAnimationFrame(function () {
            drawer.classList.add('is-open');
            overlay.classList.add('is-open');
        });
        drawer.setAttribute('aria-hidden', 'false');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        if (!drawer || !overlay) return;
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        window.setTimeout(function () { overlay.hidden = true; }, 300);
    }

    if (trigger) trigger.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) closeDrawer();
    });

    // ── Accordion sections ──────────────────────────────────────────────
    if (drawer) {
        drawer.querySelectorAll('.ovo-filter-section__toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                btn.closest('.ovo-filter-section').classList.toggle('is-open');
            });
        });
    }

    // ── Apply filters ────────────────────────────────────────────────────
    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            var sortInput = drawer.querySelector('input[name="ovo-sort"]:checked');
            state.sort = sortInput ? sortInput.value : 'menu_order';

            state.categories = Array.prototype.map.call(
                drawer.querySelectorAll('input[name="ovo-category"]:checked'),
                function (el) { return el.value; }
            );

            state.minPrice = minInput && minInput.value !== '' ? parseFloat(minInput.value) : null;
            state.maxPrice = maxInput && maxInput.value !== '' ? parseFloat(maxInput.value) : null;
            state.page = 1;

            fetchProducts(true);
            closeDrawer();
        });
    }

    // ── Clear all ─────────────────────────────────────────────────────────
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            var featured = drawer.querySelector('input[name="ovo-sort"][value="menu_order"]');
            if (featured) featured.checked = true;
            drawer.querySelectorAll('input[name="ovo-category"]').forEach(function (el) { el.checked = false; });
            if (minInput) minInput.value = '';
            if (maxInput) maxInput.value = '';

            state.categories = [];
            state.sort = 'menu_order';
            state.minPrice = null;
            state.maxPrice = null;
            state.page = 1;

            fetchProducts(true);
        });
    }
})();

// ── WISHLIST TOGGLE + LOGIN-REQUIRED MODAL ────────────────────────────────────
(function() {
    var PENDING_KEY = 'eskecy_pending_wish';

    function applyWishlistResult(productId, action, count) {
        // Update ALL wishlist buttons for this product (product page + all cards)
        var allWishBtns = document.querySelectorAll('[data-product-id="' + productId + '"]');
        allWishBtns.forEach(function(wb) {
            if (wb.classList.contains('wishlist-btn')) {
                var icon  = wb.querySelector('.wishlist-btn__icon');
                var label = wb.querySelector('.wishlist-btn__label');
                if (action === 'added') {
                    wb.classList.add('is-wished');
                    if (icon)  icon.textContent  = '♥';
                    if (label) label.textContent = 'Remove from Wishlist';
                    wb.setAttribute('aria-label', 'Remove from Wishlist');
                } else {
                    wb.classList.remove('is-wished');
                    if (icon)  icon.textContent  = '♡';
                    if (label) label.textContent = 'Save to Wishlist';
                    wb.setAttribute('aria-label', 'Save to Wishlist');
                }
            } else if (wb.classList.contains('product-card__wish')) {
                if (action === 'added') {
                    wb.classList.add('is-wished');
                    wb.textContent = '♥';
                    wb.setAttribute('aria-label', 'Remove from Wishlist');
                    wb.setAttribute('title', 'Remove from Wishlist');
                } else {
                    wb.classList.remove('is-wished');
                    wb.textContent = '♡';
                    wb.setAttribute('aria-label', 'Save to Wishlist');
                    wb.setAttribute('title', 'Save to Wishlist');
                }
            }
        });

        // Update header count badge
        var countEl = document.querySelector('.header-wishlist__count');
        if (countEl) {
            countEl.textContent = count;
            countEl.style.display = count > 0 ? 'flex' : 'none';
        } else if (count > 0) {
            var heartLink = document.querySelector('.header-wishlist');
            if (heartLink) {
                var span = document.createElement('span');
                span.className = 'header-wishlist__count';
                span.textContent = count;
                heartLink.appendChild(span);
            }
        }

        // If on wishlist page, remove the card
        if (action === 'removed') {
            var btn  = document.querySelector('.wishlist-remove[data-product-id="' + productId + '"]');
            var card = btn && btn.closest('.wishlist-card');
            if (card) {
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(function() { card.remove(); }, 300);
            }
        }
    }

    function toggleWishlist(productId, nonce, ajaxUrl) {
        var formData = new FormData();
        formData.append('action', 'eskecy_toggle_wishlist');
        formData.append('product_id', productId);
        formData.append('nonce', nonce);
        return fetch(ajaxUrl, { method: 'POST', body: formData }).then(function(r) { return r.json(); });
    }

    var loginModal = null;

    function openLoginModal() {
        if (!loginModal) return;
        loginModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeLoginModal(clearPending) {
        if (!loginModal) return;
        loginModal.classList.remove('is-open');
        document.body.style.overflow = '';
        if (clearPending) sessionStorage.removeItem(PENDING_KEY);
    }

    document.addEventListener('DOMContentLoaded', function() {
        loginModal = document.getElementById('wishlist-login-modal');

        if (loginModal) {
            var overlay  = document.getElementById('wishlist-login-overlay');
            var closeBtn = document.getElementById('wishlist-login-close');
            if (overlay)  overlay.addEventListener('click', function() { closeLoginModal(true); });
            if (closeBtn) closeBtn.addEventListener('click', function() { closeLoginModal(true); });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && loginModal.classList.contains('is-open')) closeLoginModal(true);
            });

            // Checkout: guest checkout is disabled, so pop the modal open
            // immediately rather than waiting for a click.
            if (loginModal.classList.contains('wishlist-login-modal--autoopen')) {
                openLoginModal();
            }
        }

        // Fallback link shown in place of the checkout form when a guest
        // dismisses the auto-opened modal (see woocommerce_checkout_must_be_logged_in_message).
        document.addEventListener('click', function(e) {
            var trigger = e.target.closest('.wishlist-login-trigger');
            if (!trigger) return;
            e.preventDefault();
            openLoginModal();
        });

        var pendingId = sessionStorage.getItem(PENDING_KEY);
        if (pendingId && window.stanrayData) {
            if (window.stanrayData.isLoggedIn) {
                var nonce   = (window.eskecyWishlist && window.eskecyWishlist.nonce) || stanrayData.nonce;
                var ajaxUrl = (window.eskecyWishlist && window.eskecyWishlist.ajaxUrl) || stanrayData.ajaxUrl;
                toggleWishlist(pendingId, nonce, ajaxUrl).then(function(data) {
                    sessionStorage.removeItem(PENDING_KEY);
                    if (data.success) applyWishlistResult(pendingId, data.data.action, data.data.count);
                    closeLoginModal(false);
                });
            } else {
                // Came back here after a failed login/register submission — reopen with the error shown.
                openLoginModal();
            }
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.wishlist-btn, .wishlist-remove, .product-card__wish');
            if (!btn) return;
            e.preventDefault();

            var productId = btn.dataset.productId;
            var nonce     = btn.dataset.nonce || (window.eskecyWishlist && window.eskecyWishlist.nonce);
            var ajaxUrl   = (window.eskecyWishlist && window.eskecyWishlist.ajaxUrl) || (window.stanrayData && window.stanrayData.ajaxUrl);
            if (!productId || !ajaxUrl) return;

            btn.style.opacity = '0.6';
            btn.disabled = true;

            toggleWishlist(productId, nonce, ajaxUrl)
                .then(function(data) {
                    if (!data.success) {
                        if (data.data && data.data.code === 'login_required') {
                            sessionStorage.setItem(PENDING_KEY, productId);
                            openLoginModal();
                        }
                        return;
                    }
                    applyWishlistResult(productId, data.data.action, data.data.count);
                })
                .catch(function() {})
                .finally(function() {
                    btn.style.opacity = '';
                    btn.disabled = false;
                });
        });
    });
})();

// ── CUSTOMER REVIEWS SLIDER ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('.js-cr-slider')) return;
    var crSwiper = new Swiper('.js-cr-slider', {
        slidesPerView: 'auto',
        spaceBetween: 24,
        loop: true,
        speed: 4000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
        },
        freeMode: {
            enabled: true,
            momentum: false,
        },
        pagination: {
            el: '.js-cr-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.cr__btn--next',
            prevEl: '.cr__btn--prev',
        },
        grabCursor: true,
        breakpoints: {
            0:    { spaceBetween: 12 },
            480:  { spaceBetween: 16 },
            768:  { spaceBetween: 20 },
            1024: { spaceBetween: 24 },
        },
    });

    // Pause on hover, resume on leave
    var sliderEl = document.querySelector('.js-cr-slider');
    sliderEl.addEventListener('mouseenter', function () { crSwiper.autoplay.stop(); });
    sliderEl.addEventListener('mouseleave', function () { crSwiper.autoplay.start(); });
});

// ── HERO BANNER: Mouse Parallax ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Skip for users who prefer reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var section = document.querySelector('.hb');
    if (!section) return;

    var wm   = section.querySelector('.hb__wm');
    var wrap = section.querySelector('.hb__product-wrap');
    if (!wm && !wrap) return;

    // After entry animations finish, clear them so CSS variables drive the transform
    if (wm)   wm.addEventListener('animationend',   function () { wm.style.animation   = 'none'; }, { once: true });
    if (wrap) wrap.addEventListener('animationend', function () { wrap.style.animation = 'none'; }, { once: true });

    var targetWmX = 0, targetWmY = 0;
    var targetImgX = 0, targetImgY = 0;
    var currentWmX = 0, currentWmY = 0;
    var currentImgX = 0, currentImgY = 0;
    var rafId = null;

    var WM_STRENGTH  = 45;  // ESKECY text — wide, dramatic shift
    var IMG_STRENGTH = 28;  // product image — tighter shift

    section.addEventListener('mousemove', function (e) {
        var rect = section.getBoundingClientRect();
        var nx = (e.clientX - rect.left)  / rect.width  - 0.5;
        var ny = (e.clientY - rect.top)   / rect.height - 0.5;
        targetWmX  = nx * WM_STRENGTH;
        targetWmY  = ny * WM_STRENGTH;
        targetImgX = nx * IMG_STRENGTH;
        targetImgY = ny * IMG_STRENGTH;
        if (!rafId) rafId = requestAnimationFrame(tick);
    });

    section.addEventListener('mouseleave', function () {
        targetWmX = targetWmY = targetImgX = targetImgY = 0;
        if (!rafId) rafId = requestAnimationFrame(tick);
    });

    function lerp(a, b, t) { return a + (b - a) * t; }

    function tick() {
        rafId = null;
        currentWmX  = lerp(currentWmX,  targetWmX,  0.07);
        currentWmY  = lerp(currentWmY,  targetWmY,  0.07);
        currentImgX = lerp(currentImgX, targetImgX, 0.1);
        currentImgY = lerp(currentImgY, targetImgY, 0.1);

        section.style.setProperty('--hb-wm-x',  currentWmX.toFixed(2)  + 'px');
        section.style.setProperty('--hb-wm-y',  currentWmY.toFixed(2)  + 'px');
        section.style.setProperty('--hb-img-x', currentImgX.toFixed(2) + 'px');
        section.style.setProperty('--hb-img-y', currentImgY.toFixed(2) + 'px');

        var stillMoving =
            Math.abs(currentWmX  - targetWmX)  > 0.05 ||
            Math.abs(currentWmY  - targetWmY)  > 0.05 ||
            Math.abs(currentImgX - targetImgX) > 0.05 ||
            Math.abs(currentImgY - targetImgY) > 0.05;

        if (stillMoving) rafId = requestAnimationFrame(tick);
    }
});

// ── CUSTOM CURSOR ────────────────────────────────────────────────────────────
(function () {
    // Touch devices keep native behaviour
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) return;

    var html = document.documentElement;
    html.classList.add('has-custom-cursor');

    var cc = document.createElement('div');
    cc.className = 'cc';
    cc.setAttribute('aria-hidden', 'true');
    cc.innerHTML = '<div class="cc__ring"></div><div class="cc__dot"></div><div class="cc__label">VIEW</div>';
    document.body.appendChild(cc);

    var ring  = cc.querySelector('.cc__ring');
    var dot   = cc.querySelector('.cc__dot');
    var label = cc.querySelector('.cc__label');

    var mx = -200, my = -200;
    var rx = -200, ry = -200;
    var rafId = null;

    document.addEventListener('mousemove', function (e) {
        mx = e.clientX;
        my = e.clientY;
        cc.classList.add('is-visible');
        if (!rafId) rafId = requestAnimationFrame(animRing);
    });

    function animRing() {
        rafId = null;
        rx += (mx - rx) * 0.13;
        ry += (my - ry) * 0.13;
        var pos = rx.toFixed(2) + 'px';
        var posY = ry.toFixed(2) + 'px';
        ring.style.left  = pos;
        ring.style.top   = posY;
        dot.style.left   = pos;
        dot.style.top    = posY;
        label.style.left = pos;
        label.style.top  = posY;
        if (Math.abs(rx - mx) > 0.3 || Math.abs(ry - my) > 0.3) {
            rafId = requestAnimationFrame(animRing);
        }
    }

    // Expand ring on interactive elements
    document.addEventListener('mouseover', function (e) {
        if (e.target.closest('a, button, [role="button"], input, select, textarea, label')) {
            html.classList.add('cc-on-interactive');
        }
        // VIEW cursor on images
        if (e.target.closest('img, .hb__product-wrap, .product-card__image-wrap, .collection-card__img, .editorial__img')) {
            html.classList.add('cc-on-image');
            html.classList.remove('cc-on-interactive');
        }
    });
    document.addEventListener('mouseout', function (e) {
        if (e.target.closest('a, button, [role="button"], input, select, textarea, label')) {
            html.classList.remove('cc-on-interactive');
        }
        if (e.target.closest('img, .hb__product-wrap, .product-card__image-wrap, .collection-card__img, .editorial__img')) {
            html.classList.remove('cc-on-image');
        }
    });

    document.addEventListener('mouseleave', function () { cc.classList.remove('is-visible'); });
    document.addEventListener('mouseenter', function () { cc.classList.add('is-visible'); });

    /* ── PASSWORD SHOW/HIDE TOGGLE ─────────────────────────────── */
    document.addEventListener('click', function (e) {
        const toggle = e.target.closest('.stanray-toggle-password');
        if (!toggle) return;

        const wrap  = toggle.closest('.stanray-input-wrap');
        const input = wrap ? wrap.querySelector('input') : null;
        if (!input) return;

        const isHidden = input.getAttribute('type') === 'password';
        input.setAttribute('type', isHidden ? 'text' : 'password');
        toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
        toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        toggle.querySelector('.stanray-toggle-password__icon-eye').hidden = isHidden;
        toggle.querySelector('.stanray-toggle-password__icon-eye-off').hidden = !isHidden;
    });
})();

// ── CHECKOUT ADDRESS PICKER ───────────────────────────────────────────────────
// Picking a saved card only fills the same billing_*/shipping_* inputs core's
// own checkout form already renders — submission is untouched. Uses jQuery
// (not vanilla DOM events) because core's country-select.js listens for
// jQuery 'change' on #billing_country/#shipping_country to rebuild the state
// field, and re-inits select2 on it itself; triggering that reliably needs
// jQuery's own event dispatch, not a native dispatchEvent.
jQuery(function($) {

    function fieldsContainer(type) {
        return type === 'billing' ? $('.woocommerce-billing-fields') : $('div.shipping_address');
    }

    function cardData($card) {
        var data = {};
        $.each($card.get(0).attributes, function(_, attr) {
            if (attr.name.indexOf('data-') === 0) {
                data[attr.name.slice(5).replace(/-/g, '_')] = attr.value;
            }
        });
        return data;
    }

    function fillFromCard(type, $card) {
        var prefix = type + '_';
        var data = cardData($card);

        // Country must be set (and its change event fired) before the state
        // field — core's country-select.js replaces that field's DOM node
        // synchronously on country change, so anything set on it beforehand
        // would be wiped out.
        if (data.country) {
            $('#' + prefix + 'country').val(data.country).trigger('change');
        }
        $.each(data, function(key, value) {
            if (key === 'country') return;
            var $field = $('#' + prefix + key);
            if (!$field.length) return;
            $field.val(value);
            if (key === 'state') $field.trigger('change');
        });
    }

    function setManualFieldsVisible(type, visible) {
        // A CSS class + !important (not .toggle()/.hide(), which set inline
        // display) — core's address-i18n.js re-shows locale-dependent fields
        // (address_1/city/state/postcode) via its own inline style whenever
        // the country field's 'change' event fires, which fillFromCard()
        // below deliberately triggers. An inline .hide() loses that fight;
        // a stylesheet !important on the container doesn't.
        fieldsContainer(type).toggleClass('address-picker-active', ! visible);
        var $saveToggle = fieldsContainer(type).find('.address-picker__save-toggle');
        $saveToggle.toggle(visible);
        if (!visible) $saveToggle.find('input[type="checkbox"]').prop('checked', false);
    }

    function applyPickerSelection(type) {
        var $picker = fieldsContainer(type).find('.address-picker');
        if (!$picker.length) return;

        var $checked = $picker.find('input[type="radio"]:checked');
        $picker.find('.address-picker__card').removeClass('is-selected');
        $checked.closest('.address-picker__card').addClass('is-selected');

        if ($checked.val() === 'new') {
            setManualFieldsVisible(type, true);
        } else if ($checked.length) {
            setManualFieldsVisible(type, false);
            fillFromCard(type, $checked);
        }
    }

    function initPickers() {
        ['billing', 'shipping'].forEach(function(type) {
            applyPickerSelection(type);
        });
    }

    $(document.body).on('change', '.address-picker input[type="radio"]', function() {
        var type = $(this).closest('.address-picker').data('type');
        applyPickerSelection(type);
    });

    initPickers();

    // Browser back/forward can restore this page from bfcache with our last
    // DOM state but without re-running init — re-apply it so the picker
    // never shows stale selection/visibility.
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) initPickers();
    });
});
