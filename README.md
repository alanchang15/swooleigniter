# swooleigniter
speed up codeigniter with swoole http server

## Install

```cmd
$ cd /PATH/TO/CODEIGNITER/PROJECT
$ composer require alanchang15/swooleigniter
$ cp vendor/alanchang15/swooleigniter/config/swoole.php ./application/config/
$ cp vendor/alanchang15/swooleigniter/command ./
$ cp vendor/alanchang15/swooleigniter/console.php ./application/
$ cp vendor/alanchang15/swooleigniter/swooleigniter.php ./application/
```

## Require
* PHP >= 7.0.0
* Codeigniter Framwork >= 3.0.4
* Swoole >= 4.0.0

## Usage
```
$ php command swoole:http start
$ php command swoole:http stop
$ php command swoole:http restart
$ php command swoole:http status
$ php command swoole:http reload
$ php command swoole:http auto-reload
```

## Nginx Configuration
```
server {
    root /var/www/html;
    server_name www.domain.com;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        if (!-e $request_filename) {
             proxy_pass http://127.0.0.1:1215;
        }
    }
}
```

## Apache Configuration
```
<VirtualHost *:80>
    ServerName www.domain.com
    DocumentRoot /var/www/html
    DirectoryIndex index.html index.php

    <Directory "/var/www/html">
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
  
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_FILENAME} !-f
        RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ http://127.0.0.1:1215$1 [L,P]
    </IfModule>   
</VirtualHost>
### OR
<VirtualHost *:80>
    ServerName www.domain.com
    DocumentRoot /var/www/html
    DirectoryIndex index.html index.php

    <Directory "/var/www/html">
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>

   ProxyPass /admin !
   ProxyPass /index.html !
   ProxyPass /static !
   ProxyPass / http://127.0.0.1:9501/
</VirtualHost>
```
