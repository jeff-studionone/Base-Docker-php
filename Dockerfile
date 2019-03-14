FROM php:apache

RUN apt-get update && \
    apt-get install -y vim git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && export PATH=/root/composer/vendor/bin:$PATH \
    && composer self-update
# Get around directory structure quirk of exnteded image
RUN mkdir -p /var/www

WORKDIR /var/www
