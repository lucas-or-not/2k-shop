# Stage 1: Build the frontend
FROM node:18 AS frontend-build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Build the Laravel backend and copy the frontend build
FROM php:8.1-fpm

# Set the working directory
WORKDIR /var/www/

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    wget \
    zlib1g-dev \
    procps \
    jq \
    netcat-openbsd \
    build-essential \
    libcurl4-openssl-dev \
    bzip2 \
    libbz2-dev \
    libxml2-dev \
    libpng-dev \
    libmcrypt-dev \
    libzip-dev \
    libonig-dev \
    git \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    gd \
    pcntl \
    bcmath \
    mysqli \
    pdo_mysql \
    pdo \
    xml \
    zip \
    soap \
    mbstring

# Clean up
RUN apt-get clean && \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy the frontend build from the previous stage
COPY --from=frontend-build /app/public /var/www/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files for better caching
COPY composer.json composer.lock ./

# Install application dependencies
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy the application code
COPY . .

# Generate the autoloader
RUN composer dump-autoload --optimize

COPY docker/entry.sh /var

# Change permissions
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data && \
    chown -R www-data:www-data storage && \
    chmod +x /var/entry.sh && \
    touch /var/log/cron.log && chmod 777 /var/log/cron.log && \
    chmod -R 777 /var/www/

# Expose ports
EXPOSE 80 443

CMD ["/var/entry.sh"]
