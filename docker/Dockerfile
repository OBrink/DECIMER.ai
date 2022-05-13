ARG PHP_VERSION="7.4"
FROM php:${PHP_VERSION}-fpm

RUN apt-get update && apt-get -y --no-install-recommends install \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor\
    python3-pip \
    libjpeg-dev \
    zlib1g-dev \
    poppler-utils \
    ffmpeg \
    libsm6 \
    libxext6 \
    vim \
    && apt-get autoremove --purge -y && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN apt-get update \
     && apt-get install -y libzip-dev \
     && docker-php-ext-install zip

RUN docker-php-ext-install -j$(nproc) \
    opcache \
    intl \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

RUN pip3 install pillow
RUN pip3 install --upgrade setuptools pip
RUN pip3 install opencv-python
RUN pip3 install matplotlib
RUN pip3 install scikit-image
RUN pip3 install imantics
RUN pip3 install pdf2image
RUN pip3 install IPython
RUN pip3 install pystow
RUN pip3 install unicodedata2
RUN pip3 install efficientnet
RUN pip3 install selfies
RUN pip3 install tensorflow==2.7.0
RUN pip3 install keras==2.3.0
RUN pip3 install rdkit-pypi



RUN pecl install apcu-5.1.20 && docker-php-ext-enable apcu
COPY docker/app/php.ini   $PHP_INI_DIR/conf.d/
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer
RUN pip3 install --no-deps git+https://github.com/Kohulan/DECIMER-Image-Segmentation
WORKDIR /var/www/app
COPY . /var/www/app

RUN composer update 
ADD docker/app/supervisor.conf /etc/supervisor/conf.d/worker.conf
