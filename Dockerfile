FROM php:8.3-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    nginx git curl zip unzip libpng-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar el proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instalar dependencias de Frontend y compilar
RUN npm ci && npm run build

# Configurar permisos de Laravel
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html

# CONFIGURACIÓN DE NGINX:
# Borramos el default de Nginx y enlazamos el tuyo
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

EXPOSE 80

# COMANDO DE INICIO:
# 1. Corre migraciones (force para producción)
# 2. Inicia PHP-FPM en segundo plano
# 3. Inicia Nginx en primer plano
CMD ["sh", "-c", "php artisan migrate --force && php-fpm -D && nginx -g 'daemon off;'"]