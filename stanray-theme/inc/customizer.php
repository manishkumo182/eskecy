<?php
/**
 * Theme Customizer settings
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function stanray_customizer_register( $wp_customize ) {

    // ─── PANEL: StanRay Theme Options ────────────────────────────────────────
    $wp_customize->add_panel( 'stanray_options', [
        'title'    => __( 'Theme Options', 'stanray-custom' ),
        'priority' => 10,
    ] );

    // ─── SECTION: Header ─────────────────────────────────────────────────────
    $wp_customize->add_section( 'stanray_header', [
        'title' => __( 'Header', 'stanray-custom' ),
        'panel' => 'stanray_options',
    ] );

    $wp_customize->add_setting( 'header_announcement', [
        'default'           => "Discount 40% for first order\nFree shipping over Rs. 5000\nMade in Nepal\nLimited drops\nNew arrivals now live",
        'sanitize_callback' => 'sanitize_textarea_field',
    ] );
    $wp_customize->add_control( 'header_announcement', [
        'label'       => __( 'Marquee Messages', 'stanray-custom' ),
        'description' => __( 'One message per line — they scroll continuously across the top bar.', 'stanray-custom' ),
        'section'     => 'stanray_header',
        'type'        => 'textarea',
    ] );

    $wp_customize->add_setting( 'header_announcement_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'header_announcement_link', [
        'label'   => __( 'Announcement Bar Link', 'stanray-custom' ),
        'section' => 'stanray_header',
        'type'    => 'url',
    ] );

    // ─── SECTION: Footer ─────────────────────────────────────────────────────
    $wp_customize->add_section( 'stanray_footer', [
        'title' => __( 'Footer', 'stanray-custom' ),
        'panel' => 'stanray_options',
    ] );

    $wp_customize->add_setting( 'footer_copyright', [
        'default'           => '© ' . date('Y') . ' Your Brand. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'footer_copyright', [
        'label'   => __( 'Copyright Text', 'stanray-custom' ),
        'section' => 'stanray_footer',
        'type'    => 'text',
    ] );

    // ─── SECTION: Social Links ────────────────────────────────────────────────
    $wp_customize->add_section( 'stanray_social', [
        'title' => __( 'Social Links', 'stanray-custom' ),
        'panel' => 'stanray_options',
    ] );

    $socials = [ 'instagram', 'facebook', 'twitter', 'pinterest', 'tiktok' ];
    foreach ( $socials as $social ) {
        $wp_customize->add_setting( 'social_' . $social, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ] );
        $wp_customize->add_control( 'social_' . $social, [
            'label'   => ucfirst( $social ) . ' URL',
            'section' => 'stanray_social',
            'type'    => 'url',
        ] );
    }
}
add_action( 'customize_register', 'stanray_customizer_register' );
