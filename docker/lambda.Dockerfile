FROM php:8.2-fpm-alpine

ENV LAMBDA_TASK_ROOT=/var/task
ENV COMPOSER_ALLOW_SUPERUSER=1

USER root

WORKDIR /var/task

RUN cp "/etc/ssl/cert.pem" /opt/cert.pem

COPY runtime/bootstrap /opt/bootstrap
COPY runtime/bootstrap.php /opt/bootstrap.php
COPY docker/php.ini /usr/local/etc/php/php.ini

RUN chmod 755 /opt/bootstrap
RUN mkdir /opt/extensions

ENTRYPOINT ["/opt/bootstrap"]

COPY ./ /var/task

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN composer install --no-dev

CMD [ "app.handler" ]
