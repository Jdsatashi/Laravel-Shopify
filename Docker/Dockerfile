FROM php:8.1-fpm

# Install extension
RUN apt-get update && apt-get install -y \
        curl \
        git \
        unzip \
        libssl-dev \
        && apt-get clean \
        && apt-get install -y sqlite3 libsqlite3-dev \
        && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
        && docker-php-ext-install pdo pdo_sqlite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Create SQLite database directory
RUN mkdir -p /var/www

# Set permissions for SQLite database directory
RUN chown -R www-data:www-data /var/www

RUN mkdir -p /var/www/storage
RUN chmod -R gu+w /var/www/storage
RUN chmod -R guo+w /var/www/storage

# Copy entrypoint.sh into the container
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set execute permissions for entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www

# Set entrypoint to execute entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
