FROM dunglas/frankenphp:1-php8.4-trixie AS base

ARG UID=1000
ARG GID=1000
ENV TZ=UTC
ENV COMPOSER_HOME="/tmp/composer"

WORKDIR /app

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Общие пакеты и расширения для web и horizon.
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pcntl \
    opcache \
    pdo \
    pdo_pgsql \
    pgsql \
    redis

RUN if ! getent group ${GID} >/dev/null; then groupadd -g ${GID} appgroup; fi \
    && useradd -m -u ${UID} -g ${GID} appuser

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chown -R ${UID}:${GID} "/usr/local/bin/composer" \
    && chmod -R 755 "/usr/local/bin/composer"

FROM base AS web

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    aspell \
    aspell-en \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    gd \
    zip \
    intl

COPY ./.docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chown -R ${UID}:${GID} "/etc/supervisor/conf.d/supervisord.conf" \
    && chmod -R 755 "/etc/supervisor/conf.d/supervisord.conf" \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R ${UID}:${GID} /config/caddy /data/caddy

USER ${UID}

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

FROM web AS web-dev

USER root
RUN install-php-extensions xdebug
COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER ${UID}

FROM base AS horizon

USER ${UID}

CMD ["php", "artisan", "horizon"]

FROM horizon AS horizon-dev

USER root
RUN install-php-extensions xdebug
COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER ${UID}