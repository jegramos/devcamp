FROM mysql:8.1.0

LABEL authors="jegramos"

COPY docker-configs/mysql.cnf /etc/my.cnf
