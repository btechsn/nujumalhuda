FROM alpine:3.20

ARG MEDIAMTX_VERSION=1.11.3
ARG TARGETARCH

RUN apk add --no-cache ca-certificates curl tar \
    && case "$TARGETARCH" in \
        arm64) ARCH=arm64 ;; \
        *) ARCH=amd64 ;; \
    esac \
    && curl -fsSL -o /tmp/mediamtx.tar.gz \
        "https://github.com/bluenviron/mediamtx/releases/download/v${MEDIAMTX_VERSION}/mediamtx_v${MEDIAMTX_VERSION}_linux_${ARCH}.tar.gz" \
    && tar -xzf /tmp/mediamtx.tar.gz -C /usr/local/bin mediamtx \
    && rm /tmp/mediamtx.tar.gz \
    && chmod +x /usr/local/bin/mediamtx

COPY mediamtx.yml /mediamtx.yml.template
COPY entrypoint.sh /entrypoint.sh
COPY hooks/notify.sh /hooks/notify.sh
RUN chmod +x /entrypoint.sh /hooks/notify.sh

EXPOSE 1935 8888 8889 9997

ENTRYPOINT ["/entrypoint.sh"]
