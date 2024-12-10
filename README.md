<p align="center"><a href="https://exho.com.br" target="_blank"><img src="https://github.com/incloudsistemas/exhoestate/blob/main/public/images/i2c-logo-large.png" alt="ExhoEstate logo."></a></p>

## ExhoEstate | Innovative Real Estate Technology.

Real estate hub that integrates website (CMS), CRM, financial and marketing functionalities, built atop the <a href="https://laravel.com/" target="_blank">Laravel framework</a> and <a href="https://tallstack.dev/" target="_blank">TALL Stack</a> using <a href="https://filamentphp.com/" target="_blank">Filament V.3</a>. It offers a comprehensive solution for real estate agencies looking to optimize their processes and achieve their business goals more efficiently.

## Requirements

- SO: Windows, macOS or Linux
- Server Web: Apache or Nginx
- PHP: 8.2+
- Node.js: 12+
- Composer: 2+
- MySQL: 8+

## Installation

- Clone the repository and cd into it:

```bash
git clone https://github.com/incloudsistemas/exhoestate.git

cd exhoestate
```

- Install backend dependencies:

```bash
composer install
```

- Install frontend dependencies:

```bash
npm install
```

- Configure environment variables and generate app key:

```bash
cp .env.example .env

php artisan key:generate
```

- Create a MySQL database and update the `.env` file with the database credentials

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=name_of_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass
```

- Run migrations and seeders

```bash
php artisan migrate --seed
```

- Build frontend assets

```bash
npm run dev
```

- Start development server

```bash
php artisan serve
```

## Security Vulnerabilities

If you discover a security vulnerability within ExhoEstate, please send an e-mail to Vinícius C. Lemos via [contato@incloudsistemas.com.br](mailto:contato@incloudsistemas.com.br). All security vulnerabilities will be promptly addressed.
