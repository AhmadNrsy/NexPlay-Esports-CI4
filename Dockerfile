FROM php:8.1-cli

# Install dependencies required by CodeIgniter 4 and Composer
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl zip mysqli pdo pdo_mysql

# Get latest Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy existing application directory
COPY . .

# Adjust permissions for writable directory
RUN chmod -R 777 writable

# Install Composer dependencies (no-dev for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Expose a default port (Railway will override this with $PORT in CMD)
EXPOSE 8080

# Start the PHP built-in server serving the public directory
CMD php -S 0.0.0.0:$PORT -t public
