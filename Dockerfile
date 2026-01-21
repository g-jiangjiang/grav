# 基础镜像
FROM php:8.1-apache

# 安装 Grav 必须的系统库 (zip, png, yaml 等)
RUN apt-get update && apt-get install -y \
    vim \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libyaml-dev \
    && pecl install yaml \
    && docker-php-ext-enable yaml \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip opcache

# 开启 Apache 重写模块 (关键！否则点链接报错)
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制代码到容器
COPY . /var/www/html/

# 修正权限 (关键！否则白屏)
RUN chown -R www-data:www-data /var/www/html

# 暴露端口
EXPOSE 80