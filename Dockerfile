FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zlib1g-dev \
    python3 \
    python3-pip \
    python3-dev \
    python3-venv \
    build-essential \
    libicu-dev

# Install core PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Python dependencies with --break-system-packages flag
RUN pip3 install --upgrade pip --break-system-packages && \
    pip3 install asyncpg --break-system-packages

WORKDIR /var/www

# Copy only necessary files (excluding large data files)
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./
COPY artisan ./
COPY app/ ./app/
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY lang/ ./lang/
COPY public/ ./public/
COPY resources/ ./resources/
COPY routes/ ./routes/
COPY storage/ ./storage/
COPY tests/ ./tests/
COPY .env.example ./
COPY .gitattributes ./
COPY .gitignore ./
COPY phpunit.xml ./
COPY tailwind.config.js ./
COPY vite.config.js ./
COPY ws_server/ ./ws_server/

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install Node & build assets
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g pnpm && \
    pnpm install && \
    pnpm run build

# Set proper permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"] 