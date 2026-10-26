# WeChat Mini Program API

PHP 8.5 API backend and GitHub Actions -> Deployer 8 deployment.

## Deployment status
- Production server: separate `deploy` SSH account, release directory `/var/www/miniapp-api`.
- API web server: Nginx at `127.0.0.1:8080` (loopback only).
- **Existing sing-box REALITY on TCP 443 must remain untouched.**
- Pushes to `main` deploy automatically through GitHub Actions; manual dispatch is also available.

See `.github/workflows/deploy.yml`, `deploy.php`, and `DEPLOYMENT.md`.

## PostgreSQL

The API connects to the PostgreSQL instance on the VPS using PDO. Enable `pdo_pgsql` for both PHP 8.5 CLI and PHP-FPM before deploying. GitHub Actions enables the extension on its runner but does not install it on the VPS.

Create `/var/www/miniapp-api/shared/.env` from `.env.example` on the VPS and fill in the actual database name, application username, and password. The application reads the release's `.env`, which Deployer links to this shared file. Ensure the deploy account and PHP-FPM worker can read it; do not make it publicly readable. Provision the database and application role on the existing PostgreSQL instance before deploying.

The `.env` file uses PHP INI syntax with `INI_SCANNER_RAW`, not shell or dotenv syntax. Use `;` for comments, enclose values in double quotes, and write special characters literally without escaping or variable interpolation. All `DB_*` fields in `.env.example` are required; an empty password fails validation. `DB_SSLMODE=disable` is intended for the example's same-VPS loopback connection; configure the appropriate SSL mode for any remote database.

`GET /health/database` executes `SELECT 1` and returns HTTP 200 with `{"status":"ok","database":"ok"}` on success. Configuration or connection failures return HTTP 503 with `{"status":"error","database":"unavailable"}`; exception class and code are logged without credentials. `/health` continues to check only the application. The deployment workflow verifies both endpoints over SSH on the VPS.

Run `composer check` for local syntax checks without starting services. Verify the live connection on the VPS after deployment:

```bash
curl --fail --show-error http://127.0.0.1:8080/health/database
```

## Create a user

`POST /users` accepts JSON containing `username` and `password`. This endpoint only creates users; it does not implement login or updates.

```javascript
wx.request({
  url: `${apiBaseUrl}/users`,
  method: 'POST',
  header: { 'content-type': 'application/json' },
  data: { username: 'testuser', password: 'testpassword' },
  success(res) {
    if (res.statusCode === 201) {
      console.log(res.data); // { id: 1, username: 'testuser' }
    }
  }
});
```

The username is trimmed and must be 1–100 UTF-8 bytes; the password must be 1–72 UTF-8 bytes. Neither field may contain null bytes. Passwords are stored as bcrypt hashes and are never returned. Success returns HTTP 201 with only `id` and `username`. Invalid input returns HTTP 400, an existing username returns HTTP 409, unsupported methods return HTTP 405, and storage failures return HTTP 503.

Deployment runs `scripts/migrate.php` before publishing the release to create the `users` table if it does not exist. The configured database role needs permission to create tables in its schema. User data stays in PostgreSQL across releases and rollbacks; no data is stored in release directories. To initialize the table manually, run `/usr/bin/php8.5 /var/www/miniapp-api/current/scripts/migrate.php` on the VPS.

Do not commit production environment files, SSH private keys or database credentials.
