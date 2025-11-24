$nginxConf = @"
server {
    listen 80;
    server_name localhost;
    root /var/www/html;

    index index.php index.html index.htm;

    access_log /var/log/nginx/eyalamit_access.log;
    error_log /var/log/nginx/eyalamit_error.log;

    client_max_body_size 256M;
    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    
    # Charset settings for Hebrew/UTF-8
    charset utf-8;
    source_charset utf-8;

    location / {
        try_files `$uri `$uri/ /index.php?`$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass wordpress:9000;
        fastcgi_param SCRIPT_FILENAME `$document_root`$fastcgi_script_name;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param HTTP_PROXY "";
        # Ensure UTF-8 encoding
        fastcgi_param HTTP_ACCEPT_CHARSET "utf-8";
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        try_files `$uri `$uri/ /index.php?`$args;
        expires max;
        log_not_found off;
    }
}
"@

$confPath = Join-Path $PSScriptRoot "docs\nginx.conf"
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($confPath, $nginxConf, $utf8NoBom)
Write-Host "Fixed nginx.conf (removed BOM)"
