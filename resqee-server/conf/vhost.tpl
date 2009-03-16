php_value upload_max_filesize 200M
php_value post_max_size 210M
php_value memory_limit 250M

<VirtualHost *:80>  
    DocumentRoot "$instancePath/resqee-server/www"
    ServerName $hostname
    ServerAlias $hostname

    ErrorLog "$instancePath/resqee-server/logs/error.log"
    php_value error_log $instancePath/resqee-server/logs/php_error.log
    
    RewriteEngine On
</VirtualHost>