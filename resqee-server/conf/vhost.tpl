php_value upload_max_filesize 200M
php_value post_max_size 210M
php_value memory_limit 250M

#
# ReSQee server vhost
#
<VirtualHost *:80>  
    DocumentRoot "$instancePath/resqee-server/www"
    ServerName $serverHostname
    ServerAlias $serverHostname

    ErrorLog "$instancePath/resqee-server/logs/error.log"
    
    php_value error_log $instancePath/resqee-server/logs/php_error.log
    php_value include_path $instancePath/inc:$instancePath/resqee-server/templates       
</VirtualHost>

#
# ReSQee test client vhost
# just a random vhost to use for testing n stuff
#
<VirtualHost *:80>  
    DocumentRoot "$instancePath/resqee-client/www"
    ServerName $clientHostname
    ServerAlias $clientHostname
    
    SetEnv RESQEE_SERVER $serverHostname

    ErrorLog "$instancePath/resqee-client/logs/error.log"
    
    php_value include_path $instancePath/inc:.
    php_value error_log $instancePath/resqee-client/logs/php_error.log
</VirtualHost>
