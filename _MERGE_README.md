# FourSouls Laravel Safe Merge Overlay

This ZIP is an **overlay** for a fresh Laravel skeleton.

It was prepared from your generated FourSouls source ZIP. Your uploaded archive is **not a full Laravel skeleton** because it does not contain `artisan`, `bootstrap/`, `public/index.php`, or `vendor/`. Therefore this overlay deliberately does **not** include framework-owned files like `artisan`, `bootstrap/`, `vendor/`, `composer.lock`, or `node_modules/`.

## Safe usage in Laragon

1. Create a fresh Laravel project:

```bat
cd C:\laragon\www
composer create-project laravel/laravel foursouls
cd foursouls
```

2. Create a backup / git checkpoint before merging:

```bat
git init
git add .
git commit -m "fresh laravel skeleton"
```

3. Extract this overlay ZIP into the fresh Laravel project root:

```text
C:\laragon\www\foursouls
```

Allow files such as `app/`, `config/`, `database/`, `routes/`, `resources/`, `tests/`, and `docs/` to merge/overwrite.

4. Install required Composer packages. See `_merge_reference/composer-require-commands.bat`.

5. Configure `.env` and database.

6. Run:

```bat
composer dump-autoload
php artisan optimize:clear
php artisan about
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan test
```

## Important

- Do **not** extract this overlay into an unrelated production project.
- Do **not** overwrite `vendor/`, `bootstrap/`, `public/index.php`, or `artisan`; this overlay does not contain them.
- Original generated `composer.json` and `phpunit.xml` are included only as reference under `_merge_reference/`.
- If Laravel/Filament version APIs differ, fix errors during runtime integration one by one.
