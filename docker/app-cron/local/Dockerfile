FROM anzusystems/php:4.1.0-php83-cli-vipsffmpeg

LABEL maintainer="CloudOps Team <it.online@petitpress.sk>"
#
### Basic arguments and variables
ARG DOCKER_USER_ID
ARG DOCKER_GROUP_ID
#
### Create nonroot user with provided USER_ID and GROUP_ID
RUN create-user ${DOCKER_USER_ID} ${DOCKER_GROUP_ID}
#
### Copy configuration files in to the container
COPY --chown=user:user docker/bin docker/app-cron/local/bin /usr/local/bin/
#
### Scan ssh.dev.azure.com for ssh keys and basic user setup
USER user
RUN mkdir -p ~/.ssh && \
    chmod 700 ~/.ssh && \
    ssh-keyscan -t rsa,dsa \
        git.smeonline.sk \
        gitlab.smeonline.sk \
        ssh.dev.azure.com  2>&1 >> ~/.ssh/known_hosts
#
### Start
USER root
CMD ["start-command"]
