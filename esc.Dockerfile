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
    apache2-bin apache2-utils apache2 default-jre icedtea-netx

RUN apt -y install nano hostname wget unzip zip git net-tools openssl systemd supervisor telnet iputils-ping
RUN apt -y install clamav clamav-daemon tree tcpdump
# Gitflow installer
COPY ./bin/gitflow-installer.sh /root/gitflow-installer.sh
RUN chmod +x /root/gitflow-installer.sh
RUN PREFIX=/usr /root/gitflow-installer.sh install stable

# Install MongoDB.
RUN apt -y update && apt -y install mariadb-server mariadb-client

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod 755 /usr/local/bin/composer
RUN cp -ps /usr/local/bin/composer /bin/composer
ENV PATH="/usr/local/bin/composer:${PATH}"

RUN  apt-get -y install nano hostname wget unzip zip git net-tools sudo systemd

RUN mkdir -p /data/logs \
    mkdir -p /data/logs/apache2 && chown -R www-data:www-data /data/logs/apache2 && chmod -R 775 /data

RUN echo '#' >> /etc/sudoers
RUN echo 'escnode ALL=(ALL) NOPASSWD: ALL' >> /etc/sudoers

RUN mkdir -p /var/run/clamav/ && chmod -R 755 /var/run/clamav && touch /var/lib/clamav/clamd.sock && \
    chown clamav:clamav /var/lib/clamav/clamd.sock

COPY ./bin/esc/srm-1.2.15-1.el7.x86_64.rpm /root/srm-1.2.15-1.el7.x86_64.rpm
RUN apt -y install alien && alien -i /root/srm-1.2.15-1.el7.x86_64.rpm
RUN rm -f /root/srm-1.2.15-1.el7.x86_64.rpm

RUN systemctl enable mariadb
RUN systemctl enable apache2
RUN systemctl enable clamav-daemon
RUN freshclam
RUN systemctl enable clamav-freshclam

RUN apt -y install openjdk-8-jre
ENV JAVA_HOME=/usr/lib/jvm/java-8-openjdk-amd64/jre/bin/java

ARG ESC_NAME=ESCNODE_410
ARG ESC_VERSION=4.1.0
ARG MYSQL_HOST=localhost
ARG MYSQL_PORT=3306
ARG ESC_MYSQL_USER=escClient
ARG ESC_MYSQL_PASSWORD=escClient1234
ARG SSL_CERTIFICAT_SYSTEM_PEM=(</home/escnode/generationsCertificats/finalSystem/sslSysteme.pem)

ENV ESC_NAME=$ESC_NAME
ENV ESC_VERSION=$ESC_VERSION
ENV MYSQL_HOST=$MYSQL_HOST
ENV MYSQL_PORT=$MYSQL_PORT
ENV ESC_MYSQL_USER=$ESC_MYSQL_USER
ENV ESC_MYSQL_PASWORD=$ESC_MYSQL_PASSWORD
ENV SSL_CERTIFICAT_SYSTEM_PEM=$SSL_CERTIFICAT_SYSTEM_PEM
ENV CERTIFICAT_SYSTEM=$SSL_CERTIFICAT_SYSTEM_PEM

RUN export ESC_VERSION_NUMBER=$(echo $ESC_VERSION | sed 's/[^0-9]*//g')

#Création de user
RUN useradd -ms /bin/bash escnode
RUN usermod -a -G root,sudo,www-data,escnode,ssh,users,clamav escnode

# Création de répertoire de travail pour l'esc
RUN mkdir -p /STORE
RUN mkdir -p /LOGS
RUN mkdir -p /data/logs/esc
RUN mkdir -p /data/esc
RUN chown -R escnode:escnode /STORE
RUN chown -R escnode:escnode /LOGS
RUN chown -R escnode:escnode /data/logs/esc
RUN chown -R escnode:escnode /data/esc

RUN mkdir -p /home/escnode/esc
ADD --chown=escnode:escnode ./config/esc/config.xml /home/escnode/esc/config.xml
ADD --chown=escnode:escnode ./bin/esc/esc-${ESC_VERSION}/EscNode.jar /home/escnode/esc/EscNode.jar
ADD --chown=escnode:escnode ./config/esc/logback.xml /home/escnode/esc/logback.xml
ADD --chown=escnode:escnode ./bin/install_escnode.sh /home/escnode/esc/install_escnode.sh
ADD --chown=escnode:escnode ./config/esc/certificats /home/escnode/generationsCertificats
RUN bash /home/escnode/generationsCertificats/generateCertificats.sh
RUN chmod +x /home/escnode/esc/install_escnode.sh
#RUN sed -E -i -e "s/(.*)SSL_CERTIFICAT_SYSTEM_PEM(.*)$/$${SSL_CERTIFICAT_SYSTEM_PEM}\1/g" /home/escnode/esc/config.xml
RUN sed -E -i -e "s/(.*)<esc name=\"ESC_NODE_NAME\">(.*)$/        <esc name=\"${ESC_NAME}\">\1/g" /home/escnode/esc/config.xml
RUN sed -E -i -e "s/(.*)<hostname>ESC_MYSQL_HOST<\/hostname>(.*)$/                    <hostname>${MYSQL_HOST}<\/hostname>\1/g" /home/escnode/esc/config.xml
RUN sed -E -i -e "s/(.*)<port>ESC_MYSQL_PORT<\/port>(.*)$/                    <port>${MYSQL_PORT}<\/port>\1/g" /home/escnode/esc/config.xml
RUN sed -E -i -e "s/(.*)<user>ESC_MYSQL_USER<\/user>(.*)$/                    <user>${ESC_MYSQL_USER}<\/user>\1/g" /home/escnode/esc/config.xml
RUN sed -E -i -e "s/(.*)<password>ESC_MYSQL_PASSWORD<\/password>(.*)$/                    <password>${ESC_MYSQL_PASSWORD}<\/password>\1/g" /home/escnode/esc/config.xml
RUN rm -rf /home/escnode/generationsCertificats/*.key && \
    rm -rf /home/escnode/generationsCertificats/*.crt

ADD ./bin/escnode.service /home/escnode/esc/escnode.service
ADD ./bin/escnode.service /lib/systemd/system/escnode.service
RUN chmod 0644 /lib/systemd/system/escnode.service
RUN ln -s /lib/systemd/system/escnode.service /etc/systemd/system/escnode.service
RUN ln -s /lib/systemd/system/systemd-sysctl.service /etc/systemd/system/systemd-sysctl.service
RUN systemctl enable escnode
RUN systemctl enable systemd-sysctl

VOLUME ["/sys/fs/cgroup", "/tmp", "/run"]
CMD ["/usr/sbin/init", "/lib/systemd/systemd", "/usr/sbin/service mysql start", "mysql -e \"CREATE USER IF NOT EXISTS 'escClient'@'%' IDENTIFIED BY 'escClient1234';\"", "mysql -e \"GRANT ALL PRIVILEGES ON *.* TO 'escClient'@'%';\"", "/home/escnode/esc/install_escnode.sh"]

EXPOSE 22
EXPOSE 80
EXPOSE 443
EXPOSE 4100
EXPOSE 4110
EXPOSE 10443

# Define default command.
ENTRYPOINT ["/lib/systemd/systemd"]