FROM php:8.3-cli

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar y instalar dependencias
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY package.json package-lock.json ./
RUN npm ci

# Copiar código
COPY . .

# Build assets
RUN npm run build

# Permisos
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && chmod -R 777 storage bootstrap/cache

# Puerto
EXPOSE 8000

# Inicio sin artisan serve
CMD php artisan migrate --force && php -S 0.0.0.0:${PORT:-8000} -t public