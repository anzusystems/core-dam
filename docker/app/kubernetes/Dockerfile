FROM petitpress.azurecr.io/php82:1.3.0-fpm-nginx-vipsffmpeg

LABEL maintainer="Lubomir Stanko <lubomir.stanko@petitpress.sk>"
#
### Basic arguments and variables
ARG GIT_VERSION
ENV APP_VERSION=${GIT_VERSION}
#
### Copy project and configuration files in to the container
COPY --chown=user:user .                                    .
COPY --chown=user:user docker/bin docker/app/kubernetes/bin /usr/local/bin/
#
### Start
CMD ["start-command"]
