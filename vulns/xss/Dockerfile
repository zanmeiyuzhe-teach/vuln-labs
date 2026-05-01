FROM php:8.2-apache

# Install MySQL extension for stored XSS lab
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Install MariaDB for stored XSS
RUN apt-get update && apt-get install -y mariadb-server && rm -rf /var/lib/apt/lists/*

# Copy all difficulty levels
COPY easy/ /var/www/html/easy/
COPY simple/ /var/www/html/simple/
COPY hard/ /var/www/html/hard/
COPY hell/ /var/www/html/hell/

# Copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create uploads directory for stored XSS
RUN mkdir -p /var/www/html/uploads && chown www-data:www-data /var/www/html/uploads

WORKDIR /var/www/html

EXPOSE 80

CMD ["/entrypoint.sh"]
