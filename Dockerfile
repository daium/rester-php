FROM rester/rester-docker
MAINTAINER Kevin Park<kevinpark@webace.co.kr>

RUN mkdir /var/www/cfg
RUN mkdir /var/www/src
RUN mkdir /var/www/rester-php
RUN mkdir /var/www/rester-core
RUN mkdir /var/www/exten_lib

ADD cfg /var/www/cfg
ADD src /var/www/src
ADD rester-php /var/www/rester-php
ADD rester-core /var/www/rester-core
ADD nginx-conf/default.conf /etc/nginx/sites-available/default.conf
ADD nginx-conf/default-ssl.conf /etc/nginx/sites-available/default-ssl.conf

VOLUME ["/var/www/cfg"]
VOLUME ["/var/www/src"]
VOLUME ["/var/www/rester-php"]
VOLUME ["/var/www/rester-core"]
VOLUME ["/var/www/exten_lib"]

CMD mkdir /var/www/exten_lib/aws
CMD cd /var/www/exten_lib/aws
CMD composer require aws/aws-sdk-php