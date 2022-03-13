FROM php:7.4-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions for php
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Assign permissions of the working directory to the www-data user
RUN chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# RUN docker-compose exec app cp .env.example .env

# RUN docker-compose exec app php artisan key:generate \
#         docker-compose exec app php artisan config:cache \
#         docker-compose exec app php artisan storage:link


#RUN docker-compose exec db mysql -u root -p
#RUN stworchwk
#RUN docker-compose exec db mysql CREATE USER 'iigtest'@'localhost' IDENTIFIED WITH mysql_native_password BY 'stworchwk';
#RUN docker-compose exec db mysql GRANT ALL ON iigtest.* TO 'iigtest'@'localhost';
#RUN docker-compose exec db mysql FLUSH PRIVILEGES; \
#        docker-compose exec db mysql exit \
#        docker-compose exec db mysql exit
