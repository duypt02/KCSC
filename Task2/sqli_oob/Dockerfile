FROM ubuntu:latest

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update
RUN apt-get install -y apache2 sqlite3 libsqlite3-dev php libapache2-mod-php php8.1-sqlite3 netcat
RUN rm -rf /var/lib/apt/lists/*


RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 777 /var/www/html
COPY ./duy.vn /var/www/html
RUN chmod 777 /var/www/html/usersb.sqlite3
RUN sed -i 's/DirectoryIndex index.html/DirectoryIndex index.php index.html/g' /etc/apache2/mods-enabled/dir.conf

RUN mkdir public

CMD ["apache2ctl", "-D", "FOREGROUND"]

