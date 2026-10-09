FROM postgis/postgis:16-3.5

USER root

RUN sed -i 's/^# \(fr_FR.UTF-8 UTF-8\)/\1/' /etc/locale.gen \
    && sed -i 's/^# \(ar_SA.UTF-8 UTF-8\)/\1/' /etc/locale.gen \
    && locale-gen
