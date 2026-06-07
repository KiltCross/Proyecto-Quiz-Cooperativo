FROM php:8.0-apache
WORKDIR /var/www/html

RUN mkdir -p /var/www/html/Quiz-Cooperativo
#COPY ./* PHP/
#COPY Practico8/ Practico8
#COPY src/ src
RUN docker-php-ext-configure mysqli \
&& docker-php-ext-install -j4 mysqli


EXPOSE 80
