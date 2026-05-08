# استخدم نسخة أباشي لأنها تحتوي على بيئة تشغيل متكاملة
FROM php:8.2-apache

# تثبيت المكتبات المطلوبة للنظام أولاً لضمان نجاح تثبيت الإضافات
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

# تفعيل خاصية إعادة التوجيه (مهمة لملفات PHP)
RUN a2enmod rewrite

# مجلد العمل الافتراضي في أباشي
WORKDIR /var/www/html

# نسخ ملفات مشروعك
COPY . .

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html

# المنفذ 80 هو القياسي للحاويات
EXPOSE 80

# تشغيل السيرفر
CMD ["apache2-foreground"]
