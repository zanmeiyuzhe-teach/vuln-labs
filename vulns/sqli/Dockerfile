FROM php:8.2-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Install MySQL server
RUN apt-get update && apt-get install -y mariadb-server && rm -rf /var/lib/apt/lists/*

# Copy lab files
COPY easy/ /var/www/html/

# Init script: start MySQL, create database, start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

CMD ["/entrypoint.sh"]
