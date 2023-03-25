# Pull base image.
FROM ubuntu:20.04

# Fix timezone issue
ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install.
RUN \
  sed -i 's/# \(.*multiverse$\)/\1/g' /etc/apt/sources.list && \
  apt-get update && \
  apt-get -y upgrade && \
  apt-get install -y build-essential && \
  apt-get install -y software-properties-common && \
  apt-get install -y byobu curl git htop man unzip vim wget && \
  rm -rf /var/lib/apt/lists/*

# Add files.
ADD ./bin/root/.bashrc /root/.bashrc
ADD ./bin/root/.gitconfig /root/.gitconfig
ADD ./bin/root/.scripts /root/.scripts

# Install Apache
RUN add-apt-repository universe
RUN apt -y update
RUN apt -y install lsb-release ca-certificates apt-transport-https software-properties-common \
    apache2-bin apache2-utils apache2
RUN apt -y install nano hostname wget unzip zip git net-tools openssl sudo systemd supervisor \
    clamav clamav-daemon tree
# Gitflow installer
COPY ./bin/gitflow-installer.sh /root/gitflow-installer.sh
RUN chmod +x /root/gitflow-installer.sh
RUN PREFIX=/usr /root/gitflow-installer.sh install stable

COPY ./config/mongodb/mongodb.repo /etc/yum.repos.d/mongodb.repo
RUN wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | sudo apt-key add -
RUN echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/6.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-6.0.list

# Install MongoDB.
RUN apt -y update && apt -y install mongodb-org mongodb-org-server

RUN add-apt-repository ppa:ondrej/php
RUN apt -y update

RUN apt -y install php8.2 php8.2-amqp php8.2-decimal php8.2-grpc php8.2-maxminddb php8.2-opcache php8.2-redis \
    php8.2-tidy php8.2-apcu php8.2-dev php8.2-igbinary php8.2-mbstring php8.2-pcov php8.2-rrd php8.2-uopz \
    php8.2-yaml php8.2-ast php8.2-ds php8.2-imagick php8.2-mcrypt php8.2-pgsql php8.2-smbclient php8.2-uploadprogress \
    php8.2-zip php8.2-bcmath php8.2-enchant php8.2-imap php8.2-memcache php8.2-phpdbg php8.2-snmp php8.2-uuid \
    php8.2-zmq php8.2-bz2 php8.2-fpm php8.2-inotify php8.2-memcached php8.2-protobuf php8.2-soap php8.2-vips \
    php8.2-zstd php8.2-cgi php8.2-gd php8.2-interbase php8.2-mongodb php8.2-ps php8.2-solr php8.2-xdebug php8.2-cli \
    php8.2-gearman php8.2-intl php8.2-msgpack php8.2-pspell php8.2-sqlite3 php8.2-xhprof php8.2-common \
    php8.2-ldap php8.2-mysql php8.2-ssh2 php8.2-xml php8.2-curl php8.2-gmp  php8.2-lz4  php8.2-oauth \
    php8.2-raphf php8.2-swoole php8.2-xmlrpc php8.2-dba php8.2-gnupg php8.2-mailparse php8.2-odbc php8.2-readline php8.2-sybase php8.2-xsl

RUN apt -y install redis && apt -y install redis-server
RUN mkdir -p /etc/redis
RUN touch /etc/redis/redis.conf
RUN echo '#' >> /etc/redis/redis.conf
RUN echo 'maxmemory 256mb' >> /etc/redis/redis.conf
RUN echo 'maxmemory-policy allkeys-lru' >> /etc/redis/redis.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod 755 /usr/local/bin/composer
RUN cp -ps /usr/local/bin/composer /bin/composer
ENV PATH="/usr/local/bin/composer:${PATH}"

RUN  apt-get -y install nano hostname wget unzip zip git net-tools openssl sudo systemd supervisor \
    clamav clamav-daemon tree


RUN usermod -a -G root,www-data -d /home/www-data www-data
COPY --chown=www-data:www-data ./config/www-data/.ssh /home/www-data/.ssh
COPY --chown=www-data:www-data ./config/www-data/.bashrc /home/www-data/.bashrc
RUN mkdir -p /home/www-data/.composer && mkdir -p /home/www-data/.ssh && mkdir -p /data/sites/api && \
    mkdir -p /data/logs && chown -R www-data:www-data /home/www-data && chown -R www-data:www-data /data && \
    chmod -R 775 /data && mkdir -p /data/db && chown -R mongodb:mongodb /data/db

RUN mkdir -p /run/php-fpm/ && mkdir -p /run/supervisor && chown -R www-data:www-data /run/php-fpm
RUN sed -E -i -e 's/logfile=\/var\/log\/supervisor\/supervisord.log(.*)$/logfile=\/data\/logs\/supervisord.log \1/g' /etc/supervisor/supervisord.conf

RUN sed -E -i -e 's/memory_limit = 128M(.*)$/memory_limit = 4096M \1/g' /etc/php/8.2/cli/php.ini

RUN sed -E -i -e 's/<Directory \/var\/www\/>(.*)$/<Directory \/data\/sites\/> \1/g' /etc/apache2/apache2.conf

RUN mkdir -p /var/run/clamav/ && chmod -R 755 /var/run/clamav && touch /var/lib/clamav/clamd.sock && \
    chown clamav:clamav /var/lib/clamav/clamd.sock

RUN systemctl enable apache2
RUN systemctl enable mongod
RUN systemctl enable clamav-daemon
RUN freshclam
RUN systemctl enable clamav-freshclam

EXPOSE 22

EXPOSE 80

EXPOSE 443

# Define default command.
CMD ["bash"]
