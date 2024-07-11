# Install dependencies
FROM composer:2.7.7 AS vendor

ENV COMPOSER_FUND=0

WORKDIR /tmp

COPY --link composer.json composer.lock ./

RUN --mount=type=cache,target=/root/.composer \
    composer install \
    --classmap-authoritative \
    --no-interaction \
    --no-ansi \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

# Tool to install PHP extensions
FROM mlocati/php-extension-installer:2.2.18 AS php-ext-installer

# Install PHP extensions
FROM php:8.3.9-cli-alpine AS base

RUN apk add --no-cache \
    curl \
    wget \
    ca-certificates \
    supervisor \
    libsodium-dev

COPY --link --from=php-ext-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    bcmath \
    bz2 \
    exif \
    gd \
    igbinary \
    intl \
    opcache \
    pcntl \
    pdo_pgsql \
    pdo_mysql \
    pgsql \
    redis \
    sockets \
    swoole \
    uv \
    zip

RUN docker-php-source delete && \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

# Build production image
FROM base AS runner

ARG UID=1000 \
    GID=1000 \
    TZ=UTC

ENV USER=octane \
    ROOT=/var/www/html \
    OCTANE_SERVER=swoole \
    WITH_REVERB=false \
    WITH_SCHEDULER=false \
    APP_NAME=WalletWatch \
    APP_ENV=prod \
    APP_DEBUG=false

WORKDIR ${ROOT}

SHELL ["/bin/sh", "-eou", "pipefail", "-c"]

RUN arch="$(apk --print-arch)" && \
    case "$arch" in \
    armhf) _cronic_fname="supercronic-linux-arm" ;; \
    aarch64) _cronic_fname="supercronic-linux-arm64" ;; \
    x86_64) _cronic_fname="supercronic-linux-amd64" ;; \
    x86) _cronic_fname="supercronic-linux-386" ;; \
    *) echo >&2 "error: unsupported architecture: $arch"; exit 1 ;; \
    esac && \
    wget -q "https://github.com/aptible/supercronic/releases/latest/download/${_cronic_fname}" \
    -O /usr/bin/supercronic && \
    chmod +x /usr/bin/supercronic && \
    mkdir -p /etc/supercronic && \
    echo "*/1 * * * * php ${ROOT}/artisan schedule:run --no-interaction" > /etc/supercronic/laravel

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && \
    echo ${TZ} > /etc/timezone

RUN addgroup -S ${USER} -g ${GID} && \
    adduser -S ${USER} -u ${UID} -h ${ROOT} -s /bin/sh

RUN mkdir -p /var/log/supervisor /var/run/supervisor && \
    chown -R ${UID}:${GID} ${ROOT} /var/log /var/run && \
    chmod -R a+rw ${ROOT} /var/log /var/run

# Use the default production configuration
RUN mv ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

USER ${USER}

COPY --link --chown=${UID}:${GID} --from=vendor /usr/bin/composer /usr/bin/composer
COPY --link --chown=${UID}:${GID} --from=vendor /tmp/vendor ./vendor
COPY --link --chown=${UID}:${GID} . .

RUN composer dump-autoload \
    --classmap-authoritative \
    --no-interaction \
    --no-ansi \
    --no-plugins \
    --no-scripts \
    --no-dev && \
    rm -rf /usr/bin/composer

COPY --link --chown=${UID}:${GID} deployment/php.ini ${PHP_INI_DIR}/conf.d/99-octane.ini
COPY --link --chown=${UID}:${GID} deployment/supervisord.conf /etc/supervisor/
COPY --link --chown=${UID}:${GID} deployment/supervisord.*.conf /etc/supervisor/conf.d/
COPY --link --chown=${UID}:${GID} deployment/start-container /usr/local/bin/start-container

RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000 8080

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 CMD php artisan octane:status || exit 1

ENTRYPOINT ["start-container"]
