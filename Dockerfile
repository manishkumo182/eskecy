FROM wordpress:6.5-php8.2-apache

# WooCommerce-friendly PHP limits (default WP image limits are too small for
# product image uploads/imports).
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Ship the theme baked into the image so the container is self-contained on
# the VPS (no need to keep the source repo checked out on the server).
COPY . /var/www/html/wp-content/themes/stanray-theme/
