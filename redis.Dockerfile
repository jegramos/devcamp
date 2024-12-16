FROM redis:7.2.1-alpine

LABEL authors="jegramos"

COPY docker-configs/redis.conf /usr/local/etc/redis/redis.conf

# Start redis server witht the configuration we just copied
CMD ["redis-server", "/usr/local/etc/redis/redis.conf"]
