# Yu
A simple PHP framework

## Setting

Project Root Dir: /public

nginx setting:
```
location / {
    rewrite ^/(.*)$ /index.php?$1 last;
}
```
