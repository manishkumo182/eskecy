<?php
/* Template Name: Contact Page */
get_header();
?>

<main id="main" class="contact-page" role="main">
    <div class="container">

        <h1 class="contact-title">Contact Us</h1>

        <div class="contact-note">
            <p>
                To ensure you receive emails, please check your spam folder and add our email to your safe sender list.
            </p>
        </div>

        <!-- FORM -->
        <?php
        $contact_errors = $GLOBALS['contact_form_errors'] ?? [];
        $contact_sticky = $GLOBALS['contact_form_data'] ?? [];
        $reasons        = [ 'General Inquiry', 'Order Issue', 'Return' ];
        ?>

        <?php if ( isset( $_GET['success'] ) ) : ?>
            <div class="form-success">
                Message sent successfully.
            </div>
            <script>
                // Strip ?success=1 from the URL so refreshing or revisiting this link
                // doesn't keep showing the banner as if a new message were just sent.
                if ( window.history.replaceState ) {
                    var url = new URL( window.location.href );
                    url.searchParams.delete( 'success' );
                    window.history.replaceState( {}, document.title, url.toString() );
                }
            </script>
        <?php endif; ?>

        <?php if ( ! empty( $contact_errors ) ) : ?>
            <div class="form-errors">
                <ul>
                    <?php foreach ( $contact_errors as $error ) : ?>
                        <li><?php echo esc_html( $error ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="contact-form" method="post" novalidate>
            <input type="hidden" name="contact_form_submitted" value="1">
            <?php wp_nonce_field( 'stanray_contact_form', 'contact_nonce' ); ?>

            <!-- Honeypot field: hidden from real visitors via CSS, left blank by them, bots tend to fill it in. -->
            <div class="form-group contact-hp-field" aria-hidden="true">
                <label for="contact_website">Website</label>
                <input type="text" id="contact_website" name="contact_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="full_name" placeholder="Name" value="<?php echo esc_attr( $contact_sticky['name'] ?? '' ); ?>" required>
            </div>

            <div class="form-group">
                <label>Reason</label>
                <select name="reason">
                    <?php foreach ( $reasons as $reason ) : ?>
                        <option <?php selected( $contact_sticky['reason'] ?? '', $reason ); ?>><?php echo esc_html( $reason ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Email" value="<?php echo esc_attr( $contact_sticky['email'] ?? '' ); ?>" required>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" placeholder="Message"><?php echo esc_textarea( $contact_sticky['message'] ?? '' ); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Send</button>

        </form>

    </div>
</main>

<?php get_footer(); ?>