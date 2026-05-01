FROM php:8.2-apache

# Copy all difficulty levels
COPY easy/ /var/www/html/easy/
COPY simple/ /var/www/html/simple/
COPY hard/ /var/www/html/hard/
COPY hell/ /var/www/html/hell/

# Copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create some system files for inclusion
RUN echo "root:x:0:0:root:/root:/bin/bash" > /etc/passwd
RUN echo "flag{file_inclusion_easy}" > /flag_easy
RUN echo "flag{file_inclusion_hard}" > /flag_hard

WORKDIR /var/www/html

EXPOSE 80

CMD ["/entrypoint.sh"]
