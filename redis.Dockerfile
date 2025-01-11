FROM redis:7.2.1-alpine

LABEL authors="jegramos"

COPY docker-configs/redis.conf /usr/local/etc/redis/redis.conf

CMD ["redis-server", "/usr/local/etc/redis/redis.conf"]
