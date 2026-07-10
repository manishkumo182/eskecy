<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site-wrapper">

    <!-- <?php
    $announcement_raw  = get_theme_mod( 'header_announcement', '' );
    $announcement_link = get_theme_mod( 'header_announcement_link', '' );
    $marquee_items     = array_filter( array_map( 'trim', explode( "\n", $announcement_raw ) ) );
    if ( ! empty( $marquee_items ) ) :
        $marquee_tag = $announcement_link ? 'a' : 'div';
        ?>
    <div class="marquee-bar" role="banner" aria-label="<?php esc_attr_e( 'Site announcements', 'stanray-custom' ); ?>">
        <<?php echo $marquee_tag; ?> class="marquee-bar__track"<?php if ( $announcement_link ) : ?> href="<?php echo esc_url( $announcement_link ); ?>"<?php endif; ?>>
            <?php
            // Items are rendered twice back-to-back so the CSS animation (translateX -50%) loops seamlessly.
            for ( $repeat = 0; $repeat < 2; $repeat++ ) :
                foreach ( $marquee_items as $item ) : ?>
                    <span class="marquee-bar__item"><?php echo esc_html( $item ); ?></span>
                    <span class="marquee-bar__separator" aria-hidden="true">&#42;</span>
                <?php endforeach;
            endfor;
            ?>
        </<?php echo $marquee_tag; ?>>
    </div>
    <?php endif; ?> -->

    <!-- ── Site Header ───────────────────────────────────────────────────────── -->
    <header id="site-header" class="site-header" role="banner">
        <div class="header-inner">

            <!-- Left: Logo -->
            <div class="header-logo">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo__link" aria-label="<?php bloginfo( 'name' ); ?> – Home">
                    <?php
                    if ( has_custom_logo() ) {
                        the_custom_logo();
                    } else {
                        echo '<span class="header-logo__text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
                    }
                    ?>
                </a>
            </div>

            <!-- Center: Nav -->
            <nav class="header-nav" aria-label="Primary navigation">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_class'     => 'nav-list',
                    'container'      => false,
                    'depth'          => 2,
                    'fallback_cb'    => false,
                ]);
                ?>
            </nav>

            <!-- Right: Actions -->
            <div class="header-actions">
                <?php do_action( 'stanray_header_actions' ); ?>

                <button class="header-search-toggle" aria-label="Search" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>

                <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="header-account" aria-label="My Account">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </a>
                    <?php stanray_header_cart(); ?>
                <?php endif; ?>


                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-menu">
                    <span class="hamburger">
                        <span></span><span></span><span></span>
                    </span>
                </button>
            </div>

        </div><!-- .header-inner -->

        <!-- Search Bar (hidden until toggled) -->
        <div class="header-search" id="header-search" hidden>
            <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="search" class="search-field" placeholder="Search products&hellip;" value="<?php echo get_search_query(); ?>" name="s" aria-label="Search">
                <input type="hidden" name="post_type" value="product">
                <button type="submit" class="search-submit" aria-label="Submit search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </form>
        </div>

    </header><!-- #site-header -->


    <!-- Mobile Menu Drawer -->
    <div id="mobile-menu" class="mobile-menu" aria-hidden="true" role="dialog" aria-label="Mobile navigation">
        <div class="mobile-menu__overlay"></div>
        <div class="mobile-menu__drawer">
            <button class="mobile-menu__close" aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'mobile-nav-list',
                'container'      => 'nav',
                'container_class'=> 'mobile-nav',
                'container_attr' => 'aria-label="Mobile navigation"',
                'depth'          => 2,
                'fallback_cb'    => false,
            ]);
            ?>
        </div>
    </div>

    <!-- Mini Cart Drawer -->
    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
    <div id="mini-cart" class="mini-cart" aria-hidden="true" role="dialog" aria-label="Cart">
        <div class="mini-cart__overlay"></div>
        <div class="mini-cart__drawer">
            <div class="mini-cart__header">
                <h2 class="mini-cart__title">Your Cart</h2>
                <button class="mini-cart__close" aria-label="Close cart">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="mini-cart__content">
                <div class="widget_shopping_cart_content">
                    <?php woocommerce_mini_cart(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Toast notices: flushed via AJAX after cart actions so they show
         immediately near the action instead of piling up until the next
         full page load. -->
    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
    <div id="stanray-toast" class="stanray-toast" aria-live="polite" aria-atomic="true"></div>
    <?php endif; ?>
