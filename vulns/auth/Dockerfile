FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy lab files
COPY easy/ /var/www/html/easy/
COPY simple/ /var/www/html/simple/
COPY hard/ /var/www/html/hard/
COPY hell/ /var/www/html/hell/
COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Create flag file
RUN echo "CR{auth_hell_master}" > /flag

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
