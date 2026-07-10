/**
 * Eskecy Gallery Lightbox
 * Opens a full-size viewer when a ".gallery-item" is clicked
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var items = Array.prototype.slice.call(document.querySelectorAll('.gallery-item'));
        if (!items.length) return;

        var modalHTML = '<div class="gallery-lightbox" id="gallery-lightbox" role="dialog" aria-modal="true" aria-label="Image viewer">'
            + '<div class="gallery-lightbox__overlay" id="gallery-lightbox-overlay"></div>'
            + '<button class="gallery-lightbox__nav gallery-lightbox__nav--prev" id="gallery-lightbox-prev" aria-label="Previous image">&#8249;</button>'
            + '<figure class="gallery-lightbox__figure">'
            + '<img class="gallery-lightbox__img" id="gallery-lightbox-img" src="" alt="">'
            + '<figcaption class="gallery-lightbox__counter" id="gallery-lightbox-counter"></figcaption>'
            + '</figure>'
            + '<button class="gallery-lightbox__nav gallery-lightbox__nav--next" id="gallery-lightbox-next" aria-label="Next image">&#8250;</button>'
            + '<button class="gallery-lightbox__close" id="gallery-lightbox-close" aria-label="Close viewer">&times;</button>'
            + '</div>';
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        var modal   = document.getElementById('gallery-lightbox');
        var overlay = document.getElementById('gallery-lightbox-overlay');
        var img     = document.getElementById('gallery-lightbox-img');
        var counter = document.getElementById('gallery-lightbox-counter');
        var closeBtn = document.getElementById('gallery-lightbox-close');
        var prevBtn  = document.getElementById('gallery-lightbox-prev');
        var nextBtn  = document.getElementById('gallery-lightbox-next');
        var current  = 0;

        function show(index) {
            current = (index + items.length) % items.length;
            var item = items[current];
            img.src = item.getAttribute('data-full');
            img.alt = item.querySelector('img') ? item.querySelector('img').alt : '';
            counter.textContent = (current + 1) + ' / ' + items.length;
        }

        function openModal(index) {
            show(index);
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            img.src = '';
        }

        items.forEach(function(item, index) {
            item.addEventListener('click', function() {
                openModal(index);
            });
        });

        if (overlay) overlay.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (prevBtn) prevBtn.addEventListener('click', function() { show(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function() { show(current + 1); });

        document.addEventListener('keydown', function(e) {
            if (!modal.classList.contains('is-open')) return;
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') show(current - 1);
            if (e.key === 'ArrowRight') show(current + 1);
        });
    });
})();
