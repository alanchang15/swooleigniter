# Swooleigniter
speed up codeigniter with swoole

## Codeigniter Composer Configuration
```
Open the application/config/autoload.php file and add the item you want loaded to the autoload array. 
$config['composer_autoload'] = '../vendor/autoload.php'

Open the composer.json add the item.
    "extra": {
        "installer-types": ["library"],
        "installer-paths": {
            "{$name}/": ["type:library"]
        }
    }
```
## Install
```cmd
$ cd /PATH/TO/CODEIGNITER/PROJECT
$ composer require oomphinc/composer-installers-extender
$ composer require alanchang15/swooleigniter
```
## Require
* PHP >= 7.0.0
* Codeigniter Framwork >= 3.0.4
* Swoole >= 4.0.0

## Usage
```
$ php swooleigniter/command swoole:http start
$ php swooleigniter/command swoole:http stop
$ php swooleigniter/command swoole:http restart
$ php swooleigniter/command swoole:http status
$ php swooleigniter/command swoole:http reload
$ php swooleigniter/command swoole:http auto-reload
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
        proxy_set_header Host $host;
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
```
###### OR
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

   ProxyPass /admin !
   ProxyPass /index.html !
   ProxyPass /static !
   ProxyPass / http://127.0.0.1:1215/
</VirtualHost>
```
