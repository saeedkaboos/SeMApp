# استخدام نسخة أباشي لأنها الأنسب للمواقع وتدعم Render بشكل أفضل
FROM php:8.2-apache

# تثبيت التحديثات والمكتبات الضرورية لنظام التشغيل لضمان نجاح تثبيت إضافات PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# تثبيت إضافات PHP (MySQL و PostgreSQL) لضمان عمل قاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

# تفعيل مود Rewrite الخاص بأباشي لإعادة التوجيه (مهم لملفات index و login)
RUN a2enmod rewrite

# تحديد مسار العمل الافتراضي لسيرفر أباشي
WORKDIR /var/www/html

# نسخ جميع ملفات مشروعك إلى داخل الحاوية
COPY . .

# ضبط تصاريح الملفات لتتمكن أباشي من قراءتها
RUN chown -R www-data:www-data /var/www/html

# فتح المنفذ 80 (المنفذ القياسي في Render)
EXPOSE 80

# تشغيل سيرفر أباشي في الواجهة
CMD ["apache2-foreground"]
