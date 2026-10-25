# VPS and GitHub deployment checklist

**Do not modify, restart or rebind the existing sing-box REALITY TCP 443 service.** Nginx API must bind only to 127.0.0.1:8080. No public HTTPS configuration is deployed here.

## VPS tasks (Codex on VPS)
- Complete (2026-10-25 VPS handoff): PHP 8.5.12 CLI/FPM, Composer 2.10.3, rsync/ACL and Nginx installed; local Nginx and FPM started. Reboot auto-start not yet verified.
- Complete: SSH user `deploy`, authorized_keys contains verified GitHub Actions Ed25519 **public** key; dedicated-key `ssh ... whoami` returned `deploy`.
- Complete: `deploy` owns `/var/www/miniapp-api`, has no sudo, and can run PHP/Composer.
- `/var/www/miniapp-api/shared/.env` does not yet exist. The starter app has no secrets; for a real API, supply production values on VPS and restrict access appropriately before production use.
- Complete: Nginx `root /var/www/miniapp-api/current/public`; listen ONLY on `127.0.0.1:8080`. Current 404 response is expected before first release.
- Confirmed via VPS handoff: Nginx binds loopback 8080 only, and original sing-box REALITY maintains TCP 443. Never touch 443/REALITY.

## GitHub tasks
- `WECHATMINIPROGRAM`: existing deployment **private** SSH key, corresponding to VPS `deploy` authorized_keys.
- `DEPLOY_KNOWN_HOSTS`: GitHub Secret holding `202.61.241.19 ssh-ed25519 ...`; verified VPS SSH host ED25519 fingerprint: `SHA256:jsy5iONo1C2s+dll9Cw4pjB1Q7PSKr9Z9L/iThaUcCE`. User must add this Secret in GitHub, and must not use unverified `ssh-keyscan` output.
- This workflow is **manual only**. Review environment and VPS setup before merging PR and before pressing Run workflow.
- `composer.lock` was generated on a PHP 8.5 GitHub runner and committed. Deployer v8.0.5 is locked. Whenever dependencies change, regenerate and commit the lockfile.

## Validation
1. Verify SSH deploy login locally with dedicated key.
2. Verify `curl -i http://127.0.0.1:8080/health` on VPS after successful deploy; expect JSON `{"status":"ok"}`.
3. Confirm TCP 443 is still owned by original REALITY service.
4. Confirm `readlink -f /var/www/miniapp-api/current` points to a release.
5. Deploy rollback if necessary with `php vendor/bin/dep rollback production` in an authenticated local checkout.

No production credentials belong in the repo, and this initial skeleton is not yet a complete business API.
