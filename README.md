# ButterCloud Bakery

ButterCloud Bakery is a small Laravel-based online bakery application for browsing and ordering freshly baked pastries.

Repository: https://github.com/orbasidoericka/Buttercloud

## Quick Start

- Requirements: PHP 8.2+, Composer, Node.js
- Install dependencies:

```bash
composer install
npm install
```

- Copy example env and generate key:

```bash
copy .env.example .env   # Windows
php artisan key:generate
```

- Run migrations and seeders:

```bash
php artisan migrate --seed
```

- Frontend dev server (optional):

```bash
npm run dev
```

- Start the app locally:

```bash
php artisan serve
```

## Deployment

See `DEPLOYMENT.md` for detailed deployment steps (Railway recommended). The app uses `APP_NAME="ButterCloud Bakery"` in environment files.

## Contributing

Contributions are welcome — open a PR or issue on GitHub. Keep changes small and focused and add tests where appropriate.

## License

This project is licensed under the MIT license.
