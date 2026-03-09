FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Fix Apache MPM conflict
RUN a2dismod mpm_event
RUN a2enmod mpm_prefork

# Enable rewrite
RUN a2enmod rewrite

# Change Apache port to Railway port
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080