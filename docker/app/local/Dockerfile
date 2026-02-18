FROM petitpress.azurecr.io/php84:1.9.0-fpm-nginx-vipsffmpeg-xdebug

LABEL maintainer="CloudOps Team <it.online@petitpress.sk>"
#
### Basic arguments and variables
ARG DOCKER_USER
ARG DOCKER_USER_ID
ARG DOCKER_GROUP_ID
#
### Copy configuration files in to the container
COPY --chown=user:user docker/bin docker/app/local/bin /usr/local/bin/
#
### Change USER_ID and GROUP_ID for nonroot container user if needed
USER root
RUN fix-user ${DOCKER_USER} user ${DOCKER_USER_ID} ${DOCKER_GROUP_ID}
#
### Scan ssh.dev.azure.com for ssh keys and basic user setup
USER user
RUN mkdir -p ~/.ssh && \
    chmod 700 ~/.ssh && \
    ssh-keyscan -t rsa ssh.dev.azure.com 2>&1 >> ~/.ssh/known_hosts
#
### Start
CMD ["start-command"]
