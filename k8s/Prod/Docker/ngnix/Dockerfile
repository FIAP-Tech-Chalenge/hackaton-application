FROM nginx:1.21.0
RUN rm -rf /etc/nginx/conf.d/default.conf
ADD index.html /var/www/html/index.html
ADD nginx.conf /etc/nginx/conf.d/nginx.conf

# Create the storage directory and set permissions
RUN mkdir -p /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage && \
    chown -R www-data:www-data /var/www/html/storage
