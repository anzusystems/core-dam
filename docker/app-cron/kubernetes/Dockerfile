FROM petitpress.azurecr.io/php82:2.3.0-cli-vipsffmpeg

LABEL maintainer="Lubomir Stanko <lubomir.stanko@petitpress.sk>"
#
### Basic arguments and variables
ARG GIT_VERSION
ENV APP_VERSION=${GIT_VERSION}
#
### Copy project and configuration files in to the container
COPY --chown=user:user .                                         .
COPY --chown=user:user docker/bin docker/app-cron/kubernetes/bin /usr/local/bin/
#
### Start
USER root
CMD ["start-command"]
