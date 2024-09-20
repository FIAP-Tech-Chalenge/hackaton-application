# Use uma imagem base do PHP
FROM php:8.3-fpm

# Instale dependências necessárias
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && docker-php-ext-install pdo_mysql

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie os arquivos da aplicação
COPY . .

# Defina o usuário e permissões
ARG user=runner
ARG uid=1000
RUN useradd -G www-data,root -u $uid -d /home/$user $user || true
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user && \
    chown -R $user:$user /var/www/html

# Troque para o usuário não root
USER $user

# Exponha a porta
EXPOSE 9000

# Comando para iniciar o PHP-FPM
CMD ["php-fpm"]