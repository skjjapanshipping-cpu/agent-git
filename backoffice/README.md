# Laravel7.x-Roles-Permissions-Admin-Paper-Dashboard-bootstrap4+Line

* User: admin@admin.com - password

How to setup

## 1 docker build
```
docker compose build
```
## 2 docker run
```
docker compose up -d
```
## 3 หลังจากรัน Container แล้ว composer install
```
docker compose exec php composer install
```
## 4 create database
```
docker compose exec php php artisan migrate --seed
```
## Go to http://localhost


## * Laravel Log Error
```
docker compose exec php chown -R www-data:www-data /var/www/app.dev
```

