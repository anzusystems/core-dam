FROM google/cloud-sdk:329.0.0-alpine

LABEL description="Google Cloud Pub/Sub Emulator"

# ----------------------------------------------------------------------------------------------------------------------
# ENVIRONMENT VARIABLES
# ----------------------------------------------------------------------------------------------------------------------
ENV PUBSUB_LISTEN_HOST=0.0.0.0 \
    PUBSUB_LISTEN_PORT=8538 \
    PUBSUB_PROJECT_ID=emulator-project \
    PUBSUB_PROJECT_SETUP=""
ENV PUBSUB_LISTEN_ADDRESS=${PUBSUB_LISTEN_HOST}:${PUBSUB_LISTEN_PORT}

# ----------------------------------------------------------------------------------------------------------------------
# INITIAL SETUP
# Install Java 11 and Pub/Sub emulator
# ----------------------------------------------------------------------------------------------------------------------
RUN apk --no-cache --update add openjdk11-jre && \
    mkdir -p /opt/config && \
    gcloud components install pubsub-emulator beta --quiet && \
    rm -rf /var/cache/apk/* && \
    rm -rf /tmp/*

# ----------------------------------------------------------------------------------------------------------------------
# RUN
# ----------------------------------------------------------------------------------------------------------------------
EXPOSE 8538
VOLUME /opt/data

CMD ["/bin/bash", "-c", "gcloud beta emulators pubsub start --data-dir=/opt/data --host-port=${PUBSUB_LISTEN_ADDRESS}"]
