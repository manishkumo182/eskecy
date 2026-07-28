<!-- ── Footer Perks ─────────────────────────────────────────────────── -->
<section class="footer-perks" aria-label="Store guarantees">
    <div class="footer-perks__inner">

        <div class="footer-perks__item">
            <span class="footer-perks__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            </span>
            <div>
                <h4 class="footer-perks__title">Free Dispatch</h4>
                <p class="footer-perks__desc">All orders above $200</p>
            </div>
        </div>

        <div class="footer-perks__item">
            <span class="footer-perks__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>
            </span>
            <div>
                <h4 class="footer-perks__title">Easy Returns</h4>
                <p class="footer-perks__desc">30-day free returns</p>
            </div>
        </div>

        <div class="footer-perks__item">
            <span class="footer-perks__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 1 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </span>
            <div>
                <h4 class="footer-perks__title">24/7 Enquiry</h4>
                <p class="footer-perks__desc">Live chat &amp; secure tickets</p>
            </div>
        </div>

        <div class="footer-perks__item">
            <span class="footer-perks__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            </span>
            <div>
                <h4 class="footer-perks__title">Worldwide Shipping</h4>
                <p class="footer-perks__desc">Delivered to your door</p>
            </div>
        </div>

    </div>
</section>

<!-- ── Site Footer ──────────────────────────────────────────────────── -->
<footer id="site-footer" class="site-footer" role="contentinfo">

    <div class="footer-inner">

        <!-- Brand column -->
        <div class="footer-brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-brand__logo">
                <?php
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                if ( $custom_logo_id ) :
                    echo wp_get_attachment_image( $custom_logo_id, 'full', false, [ 'class' => 'footer-brand__img' ] );
                else : ?>
                    <span class="footer-brand__name">
                        <?php bloginfo( 'name' ); ?><span class="footer-brand__dot">.</span>
                    </span>
                <?php endif; ?>
            </a>

            <p class="footer-brand__tagline">
                <?php echo esc_html( get_theme_mod( 'footer_tagline', get_bloginfo( 'description' ) ) ); ?>
            </p>

            <a href="#page" class="footer-back-top" id="footer-back-top" aria-label="Back to top">
                BACK TO TOP <span class="footer-back-top__arrow">↗</span>
            </a>
        </div>

        <!-- Link columns -->
        <nav class="footer-cols" aria-label="Footer navigation">

            <!-- Shop -->
            <div class="footer-col">
                <h4 class="footer-col__title">Shop</h4>
                <ul class="footer-col__list">
                    <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">All Products</a></li>
                    <?php
                    $cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'exclude' => [ get_option( 'default_product_cat' ) ], 'number' => 5 ] );
                    if ( ! is_wp_error( $cats ) ) :
                        foreach ( $cats as $cat ) : ?>
                        <li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>

            <!-- Info -->
            <div class="footer-col">
                <h4 class="footer-col__title">Info</h4>
                <ul class="footer-col__list">
                    <?php
                    wp_list_pages( [
                        'title_li' => '',
                        'depth'    => 1,
                        'number'   => 4,
                        'exclude'  => '67,70',
                        'walker'   => new class extends Walker_Page {
                            public function start_el( &$output, $page, $depth = 0, $args = [], $current_page = 0 ) {
                                $output .= '<li><a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . esc_html( $page->post_title ) . '</a></li>';
                            }
                            public function end_el( &$output, $page, $depth = 0, $args = [] ) {}
                        },
                    ] );
                    ?>
                    
                </ul>
            </div>

            <!-- Support -->
            <div class="footer-col">
                <h4 class="footer-col__title">Support</h4>
                <ul class="footer-col__list">
                    <?php
                    $support_links = [
                        
                        'Shipping Info'   => get_theme_mod( 'footer_shipping_url', home_url( '/shipping' ) ),
                        'Refund Policy'   => get_theme_mod( 'footer_returns_url',  home_url( '/refund' ) ),
                        
                    ];
                    foreach ( $support_links as $label => $url ) : ?>
                    <li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Follow -->
            <div class="footer-col">
                <h4 class="footer-col__title">Follow</h4>
                <ul class="footer-col__list">
                    <?php
                    $socials = [
                        'Facebook'  => get_theme_mod( 'social_facebook' ),
                        'Instagram' => get_theme_mod( 'social_instagram' ),
                        'TikTok'    => get_theme_mod( 'social_tiktok' ),
                    ];
                    foreach ( $socials as $name => $url ) : ?>
                    <li><a href="<?php echo $url ? esc_url( $url ) : '#'; ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $name ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </nav>

    </div><!-- .footer-inner -->

    <!-- Bottom bar -->
    <div class="footer-bottom">
        <span class="footer-copyright">
            <?php echo esc_html( get_theme_mod( 'footer_copyright', '© ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.' ) ); ?>
        </span>
        <span class="footer-legal">
            <a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>">Privacy Policy</a>
            <a href="<?php echo esc_url( home_url( '/terms' ) ); ?>">Terms</a>
        </span>
    </div>

</footer><!-- #site-footer -->

</div><!-- #page -->

<!-- Global Loading Overlay -->
<div id="stanray-loader" class="stanray-loader" aria-hidden="true" aria-live="polite">
    <div class="stanray-loader__spinner"></div>
</div>

<!-- Global Confirm Modal — replaces native confirm() for any link/button
     marked class="js-confirm" (optionally data-confirm-message="…") -->
<div class="stanray-confirm-modal" id="stanray-confirm-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Confirm action', 'stanray-custom' ); ?>">
    <div class="stanray-confirm-modal__overlay"></div>
    <div class="stanray-confirm-modal__box">
        <p class="stanray-confirm-modal__message" id="stanray-confirm-modal__message"></p>
        <div class="stanray-confirm-modal__actions">
            <button type="button" class="stanray-btn stanray-btn--ghost" id="stanray-confirm-modal__cancel"><?php esc_html_e( 'Cancel', 'stanray-custom' ); ?></button>
            <button type="button" class="stanray-btn" id="stanray-confirm-modal__ok"><?php esc_html_e( 'Confirm', 'stanray-custom' ); ?></button>
        </div>
    </div>
</div>

<!-- WhatsApp Floating Button -->
<?php $wa = get_theme_mod( 'social_whatsapp' ); if ( $wa ) : ?>
<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $wa ) ); ?>"
   class="whatsapp-fab"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chat on WhatsApp">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12 0C5.373 0 0 5.373 0 12c0 2.107.545 4.09 1.5 5.818L0 24l6.335-1.663A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.924 0-3.727-.5-5.29-1.373L2 22l1.373-4.71A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
    </svg>
</a>
<?php endif; ?>

<!-- Back-to-top JS -->
<script>
(function(){
    var btn = document.getElementById('footer-back-top');
    if (btn) btn.addEventListener('click', function(e){
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
