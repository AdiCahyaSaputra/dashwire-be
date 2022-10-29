# Info
- [Laravel 9.19](https://laravel.com/)
- [JWT Auth 1.4.2](https://github.com/PHP-Open-Source-Saver/jwt-auth)

[Frontend UI](https://github.com/AdiCahyaSaputra/dashwire-fe)

# Run Locally

```bash

git clone https://github.com/AdiCahyaSaputra/dashwire-be
cd dashwire-be
composer install
mv .env.example .env
php artisan key:generate

```

then setup your own database config

```bash

php artisan migrate:fresh --seed
php artisan serve

```
