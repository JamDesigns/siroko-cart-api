# Use a base image with PHP 8.4 and Apache
FROM php:8.4-apache

# Install necessary dependencies for Symfony and SQLite database
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite

# Enable mod_rewrite for Symfony
RUN a2enmod rewrite

# Set the working directory to /var/www/html
WORKDIR /var/www/html/

# Copy the project contents into the container
COPY . .

# Install Composer (dependency manager for PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony dependencies with Composer
RUN composer install

# Expose the port for Symfony to run
EXPOSE 8000

# Command to run Apache in the container
CMD ["apache2-foreground"]
