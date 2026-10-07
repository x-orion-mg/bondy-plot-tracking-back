FROM registry.pulse.mg/dev/php8.1-apache-composer:1.0-bondy as base
RUN a2enmod rewrite headers ssl
COPY . .
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-scripts && composer dump-autoload --optimize
####################################################
################## TEST STAGE      #################
FROM base as test
COPY .env.test .env
RUN pecl install xdebug                    && \
    docker-php-ext-enable xdebug           && \
    echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
ENTRYPOINT []
CMD ["apache2-foreground"]

#####################################################
################## PRODUCTION STAGE #################
FROM base as production

COPY httpd.conf /etc/apache2/sites-available/httpd.conf
RUN a2ensite httpd.conf

COPY ssl/certificat.crt /etc/apache2/ssl/certificat.crt
COPY ssl/certificat.key /etc/apache2/ssl/certificat.key

RUN mv .env.template .env

RUN ln -s public bondy_back

RUN chown -R www-data:www-data . /var/www/html/vendor && \
    chmod -R 777 . /var/www/html/vendor

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
EXPOSE 443

COPY php.ini /usr/local/etc/php/
USER www-data
ENTRYPOINT [ "/usr/local/bin/entrypoint.sh" ]

CMD ["apache2-foreground"]
