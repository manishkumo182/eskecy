/* ============================================================
   ESKECY — Explore
   assets/js/explore.js
   Shared by the Explore page (page-explore.php) and the Studio
   page (page-studio.php). Door/back links do a real navigation
   between the two pages; a brief fade-out on click keeps the
   hand-off feeling like one flow instead of a hard page cut.
   Hotspot pins toggle product cards; Add to Bag posts through
   the theme's existing quick-add AJAX endpoint (inc/ajax.php:
   stanray_add_to_cart) and reuses the header cart badge updater
   already defined in main.js.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('explore-root');
    if (!root) return;

    // Any link that should fade the scene out before leaving —
    // the door, the "Click to Enter" prompt, and the back arrow.
    ['explore-door', 'explore-enter', 'explore-back'].forEach(function (id) {
        var link = document.getElementById(id);
        if (!link) return;

        link.addEventListener('click', function (e) {
            var href = link.getAttribute('href');
            if (!href) return;

            e.preventDefault();
            root.classList.add('is-leaving');
            setTimeout(function () {
                window.location.href = href;
            }, 320);
        });
    });

    // ── Hotspots: only one card open at a time ──────────────────
    root.addEventListener('click', function (e) {
        var pin = e.target.closest('.explore__pin');
        if (pin) {
            var spot = pin.closest('.explore__hotspot');
            var wasOpen = spot.classList.contains('is-open');

            root.querySelectorAll('.explore__hotspot.is-open').forEach(function (other) {
                other.classList.remove('is-open');
                other.querySelector('.explore__pin').setAttribute('aria-expanded', 'false');
            });

            if (!wasOpen) {
                spot.classList.add('is-open');
                pin.setAttribute('aria-expanded', 'true');
            }
            return;
        }

        var closeBtn = e.target.closest('.explore__card-close');
        if (closeBtn) {
            var hotspot = closeBtn.closest('.explore__hotspot');
            hotspot.classList.remove('is-open');
            hotspot.querySelector('.explore__pin').setAttribute('aria-expanded', 'false');
            return;
        }

        var addBtn = e.target.closest('.explore__card-add');
        if (addBtn) {
            handleAddToBag(addBtn);
        }
    });

    function handleAddToBag(btn) {
        if (btn.classList.contains('is-loading')) return;

        var productId = btn.dataset.productId;
        var original  = btn.textContent;
        var ajaxUrl   = (window.stanrayData && stanrayData.ajaxUrl) || '';
        var nonce     = (window.stanrayData && stanrayData.nonce) || '';

        if (!productId || !ajaxUrl) return;

        btn.classList.add('is-loading');
        btn.textContent = 'Adding…';

        var formData = new FormData();
        formData.append('action', 'stanray_add_to_cart');
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        formData.append('nonce', nonce);

        fetch(ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.classList.remove('is-loading');
                if (data && data.success) {
                    btn.textContent = 'Added ✓';
                    if (typeof stanrayFetchCartFragments === 'function' && typeof stanrayUpdateCartIcon === 'function') {
                        stanrayFetchCartFragments(stanrayUpdateCartIcon);
                    }
                    setTimeout(function () { btn.textContent = original; }, 1800);
                } else {
                    btn.textContent = (data && data.data && data.data.message) || 'Try again';
                    setTimeout(function () { btn.textContent = original; }, 1800);
                }
            })
            .catch(function () {
                btn.classList.remove('is-loading');
                btn.textContent = 'Try again';
                setTimeout(function () { btn.textContent = original; }, 1800);
            });
    }
});
