FROM php:8.2-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Install MariaDB
RUN apt-get update && apt-get install -y mariadb-server && rm -rf /var/lib/apt/lists/*

# Copy all difficulty levels
COPY easy/ /var/www/html/easy/
COPY simple/ /var/www/html/simple/
COPY hard/ /var/www/html/hard/
COPY hell/ /var/www/html/hell/

# Copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create upload directory
RUN mkdir -p /var/www/html/uploads && chown www-data:www-data /var/www/html/uploads

WORKDIR /var/www/html

EXPOSE 80

CMD ["/entrypoint.sh"]
