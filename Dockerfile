# Use an official PHP-Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite if needed (useful for .htaccess)
RUN a2enmod rewrite

# Copy all files in your repo into Apache web root
COPY . /var/www/html/

# Set ownership (optional)
RUN chown -R www-data:www-data /var/www/html/
