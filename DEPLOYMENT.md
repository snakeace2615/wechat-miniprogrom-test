# VPS and GitHub deployment checklist

**Do not modify, restart or rebind the existing sing-box REALITY TCP 443 service.** Nginx API must bind only to 127.0.0.1:8080. No public HTTPS configuration is deployed here.

## VPS tasks (Codex on VPS)
- PHP 8.5 CLI, FPM, Composer, rsync, tar and unzip ready.
- SSH user `deploy`, authorized_keys contains GitHub Actions Ed25519 **public** key.
- `deploy` owns /var/www/miniapp-api and can run PHP and composer without sudo.
- Create /var/www/miniapp-api/shared/.env (production values only on VPS), readable by the PHP worker as appropriate.
- Nginx `root /var/www/miniapp-api/current/public`; listen ONLY on `127.0.0.1:8080`. Nginx initial config test must not affect existing services.
- Confirm no other service is bound to 127.0.0.1:8080, check `nginx -t` before reload; never touch TCP 443/REALITY.

## GitHub tasks
- `WECHATMINIPROGRAM`: existing deployment **private** SSH key, corresponding to VPS `deploy` authorized_keys.
- `DEPLOY_KNOWN_HOSTS`: verified SSH host key for 202.61.241.19, obtained after checking its fingerprint via a trusted VPS console. Do not use unverified `ssh-keyscan` output.
- This workflow is **manual only**. Review environment and VPS setup before merging PR and before pressing Run workflow.
- `composer.lock` should be generated and committed by project developer with PHP 8.5 Composer before production deployment for reproducible dependencies.

## Validation
1. Verify SSH deploy login locally with dedicated key.
2. Verify `curl -i http://127.0.0.1:8080/health` on VPS after successful deploy; expect JSON `{"status":"ok"}`.
3. Confirm TCP 443 is still owned by original REALITY service.
4. Confirm `readlink -f /var/www/miniapp-api/current` points to a release.
5. Deploy rollback if necessary with `php vendor/bin/dep rollback production` in an authenticated local checkout.

No production credentials belong in the repo, and this initial skeleton is not yet a complete business API.
