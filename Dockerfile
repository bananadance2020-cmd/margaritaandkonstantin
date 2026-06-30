FROM php:8.2-apache
# Use the PORT environment variable in Apache configuration files.
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
