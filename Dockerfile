FROM dunglas/frankenphp:php8.4-bookworm

# Label metadata
LABEL maintainer="SiAbsen Dev Team"
LABEL description="FrankenPHP development environment for SiAbsen (FrankenPHP + Queue + Vite + SQLite)"

# Environment variables
ENV SERVER_NAME=":80" \
    CADDY_GLOBAL_OPTIONS="auto_https off" \
    COMPOSER_ALLOW_SUPERUSER=1

# Install system dependencies, supervisor, & PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    supervisor \
    git \
    curl \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    ca-certificates \
    && install-php-extensions \
    pdo_sqlite \
    sqlite3 \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    redis \
    gd \
    zip \
    bcmath \
    intl \
    pcntl \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js & npm (v22 LTS)
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Set working directory
WORKDIR /app

# Copy konfigurasi supervisor & entrypoint
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

# Jalankan supervisor untuk mengelola FrankenPHP, Queue worker, dan Vite bersamaan
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

