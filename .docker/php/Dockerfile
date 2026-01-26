FROM dunglas/frankenphp:1-php8.4-trixie AS app

ARG UID=1000
ARG GID=1000
ENV TZ=UTC
ENV COMPOSER_HOME="/tmp/composer"

WORKDIR /app

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Устанавливаем системные пакеты и PHP расширения в одном слое
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    aspell  \
    aspell-en \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_pgsql \
    pgsql \
    redis \
    zip \
    intl

# Создаем пользователя и группу
RUN useradd ${UID}; \
	# Add additional capability to bind to port 80 and 443
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	# Give write access to /config/caddy and /data/caddy
	chown -R ${UID}:${GID} /config/caddy /data/caddy

#RUN if getent group ${GID}; then \
#      group_name=$(getent group ${GID} | cut -d: -f1); \
#      useradd -m -u ${UID} -g ${GID} -s /bin/bash www; \
#    else \
#      groupadd -g ${GID} www && \
#      useradd -m -u ${UID} -g www -s /bin/bash www; \
#      group_name=www; \
#    fi

# Устанавливаем Composer с правильными правами доступа
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
#RUN mkdir -p "${COMPOSER_HOME}"
RUN chown -R ${UID}:${GID} "/usr/local/bin/composer" \
    && chmod -R 755 "/usr/local/bin/composer"

FROM app AS dev

# Устанавливаем xdebug
RUN install-php-extensions xdebug
COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY ./.docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chown -R ${UID}:${GID} "/etc/supervisor/conf.d/supervisord.conf" \
    && chmod -R 755 "/etc/supervisor/conf.d/supervisord.conf"

USER ${UID}

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]