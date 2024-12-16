FROM serversideup/php:8.2-fpm-nginx

LABEL authors="jegramos"

# Switch to root so we can perform actions that require root priviledges
USER root

# Install the intl extension with root permissions
RUN install-php-extensions intl gd

# Drop back to our unprivileged user
USER www-data
