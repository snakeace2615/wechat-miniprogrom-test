# WeChat Mini Program API

PHP 8.5 API backend and GitHub Actions -> Deployer 8 deployment.

## Deployment status
- Production server: separate `deploy` SSH account, release directory `/var/www/miniapp-api`.
- API web server: Nginx at `127.0.0.1:8080` (loopback only).
- **Existing sing-box REALITY on TCP 443 must remain untouched.**
- Deployment remains manual until VPS checks and SSH host identity verification are complete.

See the deployment workflow and `deploy.php` on the deployment-setup branch.

Do not commit production environment files, SSH private keys or database credentials.
