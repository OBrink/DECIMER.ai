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
    default-jre \
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

RUN pip3 install pillow==9.4.0
RUN pip3 install --upgrade setuptools pip
RUN pip3 install opencv-python==4.7.0.68
RUN pip3 install matplotlib==3.4.3
RUN pip3 install scikit-image==0.19.3
RUN pip3 install pdf2image==1.16.2
RUN pip3 install IPython==8.10.0
RUN pip3 install pystow==0.4.9
RUN pip3 install unicodedata2==15.0.0
RUN pip3 install efficientnet==1.1.1
RUN pip3 install selfies==2.1.0
RUN pip3 install tensorflow==2.7.0
RUN pip3 install keras==2.3.0
RUN pip3 install rdkit-pypi==2022.09.4
RUN pip3 install jpype1==1.4.1
RUN pip3 install protobuf==3.19.0
RUN pip3 install numpy==1.21.6
RUN pip3 install pillow-heif==0.11.1
RUN pip3 install scipy==1.9.3
RUN pip3 install --no-deps git+https://github.com/Kohulan/DECIMER-Image_Transformer@v2.3.1
RUN pip3 install --no-deps git+https://github.com/Kohulan/DECIMER-Image-Segmentation@v1.3.0
RUN pip3 install --no-deps git+https://github.com/Kohulan/Smiles-TO-iUpac-Translator@V2.0.3
RUN pip3 install --no-deps git+https://github.com/Iagea/DECIMER-Image-Classifier@146b00be2fe6f8fa6670a4255969d4747502b7f2

RUN pecl install apcu-5.1.20 && docker-php-ext-enable apcu
COPY docker/app/php.ini   $PHP_INI_DIR/conf.d/

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/app
COPY . /var/www/app

RUN composer update 
ADD docker/app/supervisor.conf /etc/supervisor/conf.d/worker.conf
