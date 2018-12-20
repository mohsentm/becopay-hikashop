FROM becopay/alpine-nginx-php

LABEL maintainer="io@becopay.com"
LABEL version="1.0.0"
LABEL description="Joomla with hikashop and becopay plugin"

ENV HIKASHOP_VERSION 4.0.1
ENV JOOMLA_VERSION 3.9.1
ENV BECOPAY_VERSION 1.0.0

ENV INSTALL_DIR /var/www/html

ADD ./php.ini /etc/php7/php.ini

ADD ./nginx.conf /etc/nginx/conf.d/default.conf

RUN chsh -s /bin/bash www-data

COPY joomla $INSTALL_DIR

RUN chown -R www-data:www-data /var/www

RUN cd $INSTALL_DIR \
    && find . -type d -exec chmod 770 {} \; \
    && find . -type f -exec chmod 660 {} \;

COPY install-joomla /usr/local/bin/install-joomla
RUN chmod +x  /usr/local/bin/install-joomla

COPY import-db.php /usr/local/bin/import-db.php

WORKDIR $INSTALL_DIR
