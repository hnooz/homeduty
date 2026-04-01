# HomeDuty — Deployment Guide

## Overview

Deployments run automatically via GitHub Actions on every push to `main`. This document covers the one-time server setup and how to manage the server manually.

---

## Prerequisites

- A DigitalOcean Ubuntu 24.04 droplet (1GB RAM minimum, 2GB recommended)
- A domain name pointed to the droplet's IP (optional but recommended for SSL)
- SSH access to the droplet as `root`

---

## 1. One-Time Server Setup

### 1.1 Edit the setup script

Open `deploy/server-setup.sh` and confirm the top-level config variables:

```bash
REPO_URL="https://github.com/hnooz/homeduty"
DOMAIN=""        # e.g. homeduty.com — leave empty to use the server IP
DB_PASS="..."    # Already set — keep it secret
```

### 1.2 Copy and run the setup script on the droplet

```bash
# From your local machine
scp -r deploy/ root@YOUR_DROPLET_IP:/tmp/homeduty-deploy/

# SSH into the droplet and run
ssh root@YOUR_DROPLET_IP
bash /tmp/homeduty-deploy/deploy/server-setup.sh
```

This will install and configure:
- PHP 8.3 + required extensions
- Composer
- Node.js 22 + npm
- MySQL 8 (with a dedicated `homeduty` database and user)
- Nginx (with the app site config)
- PHP-FPM (dedicated pool)
- Queue worker (systemd service)
- Scheduler (systemd timer, runs every minute)
- UFW firewall (SSH + HTTP/HTTPS only)
- Fail2ban (SSH brute-force protection)
- Certbot SSL (if `DOMAIN` is set)

### 1.3 Configure the environment

```bash
cp /tmp/homeduty-deploy/.env.production /var/www/homeduty/.env
nano /var/www/homeduty/.env
```

Fill in the required values:

| Key | Value |
|-----|-------|
| `APP_KEY` | Run `php artisan key:generate --show` and paste the output |
| `APP_URL` | `https://YOUR_DOMAIN` or `http://YOUR_IP` |
| `DB_PASSWORD` | Same password set in `server-setup.sh` |
| `MAIL_HOST` | Your SMTP provider host |
| `MAIL_USERNAME` | SMTP username |
| `MAIL_PASSWORD` | SMTP password |
| `MAIL_FROM_ADDRESS` | e.g. `noreply@homeduty.com` |

Then generate the app key:

```bash
cd /var/www/homeduty
php artisan key:generate
```

### 1.4 Authorize the GitHub Actions deploy key

Run this on the droplet to allow GitHub Actions to SSH in:

```bash
echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIEJMa72aijzjiUXVqVGxTpfMmU/of1T3/DvOiWdiksac homeduty-github-actions-deploy" \
  >> /home/homeduty/.ssh/authorized_keys
```

---

## 2. GitHub Actions Setup

Add the following secrets to your repository at **Settings → Secrets and variables → Actions**:

| Secret | Value |
|--------|-------|
| `DEPLOY_HOST` | Your droplet IP or domain |
| `DEPLOY_USER` | `homeduty` |
| `DEPLOY_SSH_KEY` | The private key from `deploy/DEPLOY.md` (see below) |

### Deploy SSH Private Key

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACBCTGu9moo844lF1alRsU6XzJlP6H9U9/w7zolnYpLGnAAAAKgzbssXM27L
FwAAAAtzc2gtZWQyNTUxOQAAACBCTGu9moo844lF1alRsU6XzJlP6H9U9/w7zolnYpLGnA
AAAEDDscpaSG8G05vzxiwpLsXRZnnQBc2Ev9H2zLFmRPeakUJMa72aijzjiUXVqVGxTpfM
mU/of1T3/DvOiWdiksacAAAAHmhvbWVkdXR5LWdpdGh1Yi1hY3Rpb25zLWRlcGxveQECAw
QFBgc=
-----END OPENSSH PRIVATE KEY-----
```

---

## 3. CI/CD Pipeline

The GitHub Actions workflow at `.github/workflows/deploy.yml` runs automatically on every push to `main`:

```
push to main
    │
    ▼
┌─────────────┐     fail → stops here
│  Run Tests  │ ──────────────────────►  ✗ deploy blocked
└─────────────┘
    │ pass
    ▼
┌──────────────────┐
│  Deploy via SSH  │  → runs deploy/deploy.sh on the droplet
└──────────────────┘
```

### What `deploy.sh` does on each deploy

1. Puts the app in maintenance mode
2. Pulls latest code from `main`
3. Installs Composer dependencies (production only)
4. Builds frontend assets (`npm run build`)
5. Caches config, routes, views, and events
6. Runs database migrations
7. Restarts the queue worker
8. Reloads PHP-FPM
9. Takes the app out of maintenance mode

---

## 4. Manual Deploy

If you need to deploy manually without pushing to `main`:

```bash
ssh homeduty@YOUR_DROPLET_IP
cd /var/www/homeduty
bash deploy/deploy.sh
```

---

## 5. Useful Server Commands

```bash
# View app logs
tail -f /var/www/homeduty/storage/logs/laravel.log

# Queue worker status
systemctl status homeduty-worker

# Restart queue worker
systemctl restart homeduty-worker

# View queue worker logs
tail -f /var/www/homeduty/storage/logs/queue-worker.log

# Scheduler timer status
systemctl status homeduty-scheduler.timer

# View scheduler logs
tail -f /var/www/homeduty/storage/logs/scheduler.log

# Reload Nginx
systemctl reload nginx

# PHP-FPM status
systemctl status php8.3-fpm
```

---

## 6. File Structure

```
deploy/
├── DEPLOY.md              # This file
├── server-setup.sh        # One-time server provisioning script
├── deploy.sh              # Deployment script (run on each release)
├── nginx.conf             # Nginx site configuration
├── php-fpm-pool.conf      # PHP-FPM pool configuration
├── queue-worker.service   # systemd service for queue worker
├── scheduler.service      # systemd one-shot service for scheduler
├── scheduler.timer        # systemd timer (runs scheduler every minute)
└── .env.production        # Production .env template (gitignored)

.github/
└── workflows/
    └── deploy.yml         # GitHub Actions CI/CD workflow
```
