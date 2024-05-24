# 使用官方 PHP 镜像并指定版本
FROM php:8.2-apache

# 安装 Redis 扩展和其他必要的 PHP 扩展
RUN apt-get update && apt-get install -y ethtool iproute2 git sudo libmagickwand-dev libzip-dev unzip libxslt-dev libgmp-dev libcurl4-openssl-dev libpng-dev libjpeg-dev libfreetype6-dev libbz2-dev libldap2-dev zlib1g-dev libsqlite3-dev \
    && docker-php-ext-install zip xsl gmp curl bcmath gd mysqli ldap pdo pdo_mysql pdo_sqlite soap intl pcntl \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis
RUN pecl install memcache \
    && docker-php-ext-enable memcache

# 复制自定义的 Apache 配置文件
COPY conf/apache.conf /etc/apache2/sites-available/000-default.conf

# 启用 Apache 的 mod_rewrite 模块
RUN a2enmod rewrite && a2enmod ssl

# 设置 PHP 相关配置参数
RUN echo "max_execution_time = 360" > /usr/local/etc/php/conf.d/custom-php.ini \
    && echo "memory_limit = 1G" >> /usr/local/etc/php/conf.d/custom-php.ini \
    && echo "post_max_size = 512M" >> /usr/local/etc/php/conf.d/custom-php.ini \
    && echo "upload_max_filesize = 512M" >> /usr/local/etc/php/conf.d/custom-php.ini \
    && echo "expose_php = Off" >> /usr/local/etc/php/conf.d/custom-php.ini

# 复制 PHP 源代码到容器中
COPY . /var/www/html/

RUN mkdir /var/www/.cache
RUN chown -R www-data:www-data /var/www/.cache
RUN chmod -R 755 /var/www/.cache
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# 使用 composer 安装依赖
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && cd /var/www/html/
RUN sudo -u www-data composer install

EXPOSE 80 443