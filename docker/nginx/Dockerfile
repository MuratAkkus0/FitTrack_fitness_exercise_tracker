FROM nginx:1.17-alpine

ENV PHP_FPM_HOST localhost

RUN apk add curl

COPY ./docker/nginx/nginx.conf /etc/nginx/
COPY ./docker/nginx/conf.d/ /etc/nginx/conf.d/
COPY ./docker/nginx/nginx-entrypoint.sh /usr/local/bin/

CMD ["nginx", "-g", "daemon off;"]

ENTRYPOINT [ "nginx-entrypoint.sh" ]
