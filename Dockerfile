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
RUN apt -y install lsb-release ca-certificates apt-transport-https software-properties-common apache2-bin apache2-utils apache2
#RUN apt-get install alien -y
RUN add-apt-repository ppa:ondrej/php
RUN apt -y update

RUN apt -y install php8.2 php8.2-{amqp,decimal,grpc,maxminddb,opcache,redis} \
    php8.2-{tidy,apcu,dev,igbinary,mbstring,pcov,rrd,uopz} \
    php8.2-{yaml,ast,ds,imagick,mcrypt,pgsql,smbclient,uploadprogress}  \
    php8.2-{zip,bcmath,enchant,imap,memcache,phpdbg,snmp,uuid} \
    php8.2-{zmq,bz2,fpm,inotify,memcached,protobuf,soap,vips} \
    php8.2-{zstd,cgi,gd,interbase,mongodb,ps,solr,xdebug,cli} \
    php8.2-{gearman,intl,msgpack,pspell,sqlite3,xhprof,common} \
    php8.2-{ldap,mysql,psr,ssh2,xml,curl,gmp ,lz4 ,oauth} \
    php8.2-{raphf,swoole,xmlrpc,dba,gnupg,mailparse,odbc,readline,sybase,xsl}

RUN apt -y install redis && apt -y install redis-server && apt -y install mongodb
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
    chmod -R 775 /data

RUN mkdir -p /run/php-fpm/ && mkdir -p /run/supervisor && chown -R www-data:www-data /run/php-fpm
RUN sed -E -i -e 's/logfile=\/var\/log\/supervisor\/supervisord.log(.*)$/logfile=\/data\/logs\/supervisord.log \1/g' /etc/supervisor/supervisord.conf

RUN sed -E -i -e 's/memory_limit = 128M(.*)$/memory_limit = 4096M \1/g' /etc/php/8.2/cli/php.ini

RUN sed -E -i -e 's/<Directory \/var\/www\/>(.*)$/<Directory \/data\/sites\/> \1/g' /etc/apache2/apache2.conf

RUN systemctl enable apache2

EXPOSE 22

EXPOSE 80

EXPOSE 443

# Define default command.
CMD ["bash"]
