FROM petitpress.azurecr.io/php83:1.2.0-fpm-nginx-vipsffmpeg

LABEL maintainer="CloudOps Team <it.online@petitpress.sk>"
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
