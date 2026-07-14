/**
 * Eskecy Size Guide Modal
 * Opens a size chart popup when ".size-guide-trigger" is clicked
 */
(function() {
    'use strict';

    // Build the modal HTML once
    var modalHTML = '<div class="size-guide-modal" id="size-guide-modal" role="dialog" aria-modal="true" aria-label="Size Guide">'
        + '<div class="size-guide-modal__overlay" id="size-guide-overlay"></div>'
        + '<div class="size-guide-modal__box">'
        + '<button class="size-guide-modal__close" id="size-guide-close" aria-label="Close size guide">&times;</button>'
        + '<h2>Size Guide</h2>'
        + '<table class="size-guide-table">'
        + '<thead><tr><th>Size</th><th>Chest (cm)</th><th>Waist (cm)</th><th>Length (cm)</th></tr></thead>'
        + '<tbody>'
        + '<tr><td><strong>S</strong></td><td>86–91</td><td>71–76</td><td>68</td></tr>'
        + '<tr><td><strong>M</strong></td><td>91–97</td><td>76–81</td><td>71</td></tr>'
        + '<tr><td><strong>L</strong></td><td>97–102</td><td>81–86</td><td>74</td></tr>'
        + '<tr><td><strong>XL</strong></td><td>107–112</td><td>91–97</td><td>77</td></tr>'
        + '</tbody></table>'
        + '<p class="size-guide-tip">📏 Tip: Measure around the fullest part of your chest and natural waist. If between sizes, size up for a relaxed fit.</p>'
        + '</div></div>';

    document.addEventListener('DOMContentLoaded', function() {
        // Inject modal into body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        var modal   = document.getElementById('size-guide-modal');
        var overlay = document.getElementById('size-guide-overlay');
        var closeBtn = document.getElementById('size-guide-close');

        function openModal() {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        // Open on trigger click
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('size-guide-trigger')) {
                e.preventDefault();
                openModal();
            }
        });

        // Close on overlay click
        if (overlay) overlay.addEventListener('click', closeModal);

        // Close on X button
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });
    });
})();
