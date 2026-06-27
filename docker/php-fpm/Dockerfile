ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt update
RUN apt-get install -y zlib1g-dev libzip-dev libpng-dev libwebp-dev libjpeg62-turbo-dev zip unzip bash ffmpeg cron
RUN apt-get install -y libmagickwand-dev libvips42 libvips-dev procps htop mc git libfcgi-bin
RUN apt-get install -y libpq-dev postgresql postgresql-contrib
RUN ln -sf /bin/bash /bin/sh

RUN docker-php-ext-configure gd --with-jpeg --with-webp && docker-php-ext-install gd
RUN docker-php-ext-configure opcache --enable-opcache && docker-php-ext-install opcache
RUN docker-php-ext-install exif
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN docker-php-ext-install ffi

RUN pecl install redis \
    && docker-php-ext-enable redis

ARG TZ
ENV TZ=${TZ}
RUN echo "date.timezone=${TZ}" > /usr/local/etc/php/conf.d/docker-php-ext-timezone.ini
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/docker-php-ext-memory.ini
RUN echo "max_file_uploads=256M" > /usr/local/etc/php/conf.d/docker-php-ext-upload.ini \
  && echo "post_max_size=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload.ini \
  && echo "upload_max_filesize=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload.ini

RUN echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.max_children = 16" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.start_servers = 8" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.min_spare_servers = 4" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.max_spare_servers = 8" >> /usr/local/etc/php-fpm.d/zz-docker.conf

RUN pecl install vips \
    && echo "ffi.enable=1" > /usr/local/etc/php/conf.d/ffi.ini \
    && echo "extension=vips.so" > /usr/local/etc/php/conf.d/vips.ini \
    && echo "zend.max_allowed_stack_size=-1" >> /usr/local/etc/php/conf.d/vips.ini


RUN if [ "$TARGETPLATFORM" = "linux/arm64" ]; then \
      ln -sf /usr/lib/aarch64-linux-gnu/libvips.so.42 /usr/lib/libvips.so.42; \
    else \
      ln -sf /usr/lib/x86_64-linux-gnu/libvips.so.42 /usr/lib/libvips.so.42; \
    fi

ARG PHP_XDEBUG
RUN if [ "$PHP_XDEBUG" = "1" ]; then \
      yes | pecl install xdebug \
          && echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    fi

ARG WWWUSER
RUN groupadd -g 1000 ${WWWUSER} && \
    useradd -u 1000 -ms /bin/bash -g ${WWWUSER} ${WWWUSER}
USER ${WWWUSER}