FROM serversideup/php:8.2-fpm-nginx

LABEL authors="jegramos"

# Switch to root so we can perform actions that require root priviledges
USER root

# Install the PHP extensions
RUN install-php-extensions intl gd

# Install JavaScript Dependencies
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# We'll run our own custom entry point
COPY --chmod=755 docker-configs/99-app-entrypoint.sh /etc/entrypoint.d/

# Copy source code to the created directory
COPY . /var/www/html

# Setup working directory
WORKDIR /var/www/html

# Mount the .env as a secret. See https://docs.render.com/docker-secrets
# DOCKER_BUILDKIT=1 docker build -t jegramos/webkit-api -f app.Dockerfile --secret id=_env,source=.env .
RUN --mount=type=secret,id=_env,dst=/var/www/html/.env  \
    composer install --no-dev --optimize-autoloader

# Install JS dependencies
RUN npm install && npm run build

# Change the permission for all the files and dir inside /var/www/html
RUN chown -R www-data:www-data /var/www/html

# As root, run the docker-php-serversideup-s6-init script
RUN docker-php-serversideup-s6-init

# Drop back to our unprivileged user
USER www-data
