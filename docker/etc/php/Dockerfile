FROM nanoninja/php-fpm:7.4.0

# Permission for PHP to write in volumes
RUN usermod -u 1000 www-data

# Install supervisor and cron
RUN apt-get update && apt-get install supervisor cron -y

# Setup cron jobs
RUN touch /var/log/cron.log
COPY cron-jobs /etc/cron.d/cron-jobs

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cron-jobs

# Apply cron job
RUN crontab /etc/cron.d/cron-jobs

# Supervisor logs directory
RUN mkdir -p /var/log/supervisor

# Supervisor config file
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]
