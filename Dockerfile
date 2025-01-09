FROM php:8.1-apache

# Actualiza los paquetes e instala dependencias
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install zip

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura el DocumentRoot de Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilita mod_rewrite
RUN a2enmod rewrite

# Configura el directorio de trabajo
WORKDIR /var/www/html
