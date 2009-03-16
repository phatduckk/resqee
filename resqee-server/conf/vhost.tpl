php_value upload_max_filesize 200M
php_value post_max_size 210M
php_value memory_limit 250M

<VirtualHost *:80>  
    DocumentRoot "$instancePath/www"
    ServerName $hostname
    ServerAlias $hostname

    ErrorLog "$instancePath/logs/error.log"
    php_value error_log $instancePath/logs/php_error.log       
</VirtualHost>