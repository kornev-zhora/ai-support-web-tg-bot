FROM ubuntu:24.04

LABEL maintainer="AI Support Bot"
LABEL description="Production-optimized ARM64 image for Raspberry Pi"

ARG WWWGROUP=1000
ARG NODE_VERSION=22
ARG VITE_TELEGRAM_BOT_USERNAME=placeholder
ARG VITE_APP_NAME="AI Support Bot"

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Configure apt for better reliability
RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy true;" >> /etc/apt/apt.conf.d/99custom

# Install base system packages
RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y --no-install-recommends \
       gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 \
       libcap2-bin libpng-dev python3 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Add PHP repository and install PHP with essential extensions only
RUN curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
       php8.4-cli \
       php8.4-curl \
       php8.4-mbstring \
       php8.4-xml \
       php8.4-zip \
       php8.4-bcmath \
       php8.4-intl \
       php8.4-readline \
       php8.4-sqlite3 \
       php8.4-mysql \
       php8.4-pgsql \
       php8.4-redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && npm install -g npm

# Set PHP capabilities for binding to port 80
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

# Create sail user
RUN groupadd --force -g $WWWGROUP sail \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

# Configure git
RUN git config --global --add safe.directory /var/www/html

# Copy application files
COPY --chown=sail:sail . /var/www/html

# Install Composer dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Configure npm for better reliability
RUN npm config set fetch-retry-mintimeout 20000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm config set fetch-retries 5 \
    && npm config set fetch-timeout 300000

# Install NPM dependencies and build assets with retry logic
# Note: Vite variables are baked into the build, but for bot username we can use a placeholder
# The actual bot interaction happens server-side via TELEGRAPH_BOT_NAME
RUN npm ci --production=false \
    && VITE_TELEGRAM_BOT_USERNAME="$VITE_TELEGRAM_BOT_USERNAME" VITE_APP_NAME="$VITE_APP_NAME" npm run build \
    && rm -rf node_modules \
    && npm cache clean --force

# Copy Docker configuration files
COPY docker/production/start-container /usr/local/bin/start-container
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/production/php.ini /etc/php/8.4/cli/conf.d/99-production.ini
RUN chmod +x /usr/local/bin/start-container

# Set proper permissions
RUN chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
