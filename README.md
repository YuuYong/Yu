# Yu
A simple PHP framework

## Setting

Project Root Dir: /public

nginx setting:
```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
