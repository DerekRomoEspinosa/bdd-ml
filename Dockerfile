FROM php:8.3-fpm

# Sistema
RUN apt-get update && apt-get install -y \
    nginx git curl zip unzip libpng-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && rm -rf /var/lib/apt/lists/*

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# COPIAR TODO EL PROYECTO PRIMERO 👈
COPY . .

# Composer (artisan ya existe aquí)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Frontend
RUN npm ci && npm run build

# Permisos Laravel
RUN chmod -R 777 storage bootstrap/cache

# Nginx
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
