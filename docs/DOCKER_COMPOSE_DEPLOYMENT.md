# Docker Compose Production Deployment Guide

This project uses Docker Compose with override files for clean separation between development and production environments.

## 📁 Project Structure on Raspberry Pi

```
/projects/ai-support-bot/
├── compose.yaml                      # Base configuration (dev + prod)
├── compose.override.production.yml   # Production overrides (committed)
├── compose.override.yml              # Active override (auto-generated from .production)
├── .env                              # Environment variables (auto-generated from GitHub)
└── data/                             # Persistent data (survives deployments)
    ├── database/
    │   └── database.sqlite           # SQLite database
    ├── storage/                      # Laravel storage
    ├── cache/                        # Bootstrap cache
    └── redis/                        # Redis data
```

## 🔧 How It Works

### Development (Local)

```bash
# Uses compose.yaml only (or with local compose.override.yml if you create one)
docker compose up -d

# Services:
# - ai-support-bot: built locally from docker/8.4/Dockerfile
# - pgsql: PostgreSQL database
# - redis: Redis cache
```

### Production (Raspberry Pi)

```bash
# Uses compose.yaml + compose.override.yml (renamed from .production)
docker compose up -d

# Services:
# - ai-support-bot: uses pre-built image from Docker Hub
# - pgsql: DISABLED (using SQLite instead)
# - redis: production configuration with persistent volume
```

## 🚀 Automatic Deployment Flow

When you create a GitHub release:

1. **Build Image** (GitHub Actions)
   - Builds ARM/v7 + ARM64 Docker image
   - Pushes to Docker Hub

2. **Deploy** (Raspberry Pi Runner)
   ```
   ┌─────────────────────────────────────────────┐
   │ 1. Create /projects/ai-support-bot/         │
   │ 2. Clone compose files from GitHub          │
   │ 3. Copy compose.override.production.yml     │
   │    → compose.override.yml                   │
   │ 4. Generate .env from GitHub Secrets        │
   │ 5. Pull latest Docker image                 │
   │ 6. docker compose down                      │
   │ 7. docker compose up -d                     │
   │ 8. Run migrations                           │
   └─────────────────────────────────────────────┘
   ```

## 🎯 What Gets Overridden in Production

| Setting | Development | Production (Override) |
|---------|-------------|----------------------|
| **Image** | Built locally | Pre-built from Docker Hub |
| **Database** | PostgreSQL | SQLite |
| **Volumes** | Source code mounted | Only data directories |
| **Ports** | 80 + 5173 (Vite) | 80 only |
| **Cache** | File-based | Redis |
| **Sessions** | File-based | Redis |
| **Restart** | No | unless-stopped |
| **PostgreSQL** | Running | Disabled (0 replicas) |

## 📝 Manual Deployment (if needed)

If you want to deploy manually on Raspberry Pi:

```bash
# 1. Create project directory
sudo mkdir -p /projects/ai-support-bot
cd /projects/ai-support-bot

# 2. Clone compose files
git clone --depth 1 https://github.com/BorschCode/ai-support-web-tg-bot.git temp
cp temp/compose.yaml .
cp temp/compose.override.production.yml .
rm -rf temp

# 3. Activate production override
cp compose.override.production.yml compose.override.yml

# 4. Create .env file
cat > .env <<EOF
DOCKER_IMAGE=sergeyphpdevua/ai-support-bot:latest
APP_NAME="AI Support Bot"
APP_URL=http://your-pi-ip
APP_PORT=80
APP_KEY=your-app-key
GEMINI_API_KEY=your-gemini-key
GEMINI_MODEL=gemini-2.5-flash
TELEGRAM_TOKEN=your-telegram-token
TELEGRAM_BOT_USERNAME=your_bot_username
TELEGRAPH_WEBHOOK_URL=http://your-pi-ip/webhook
MAIL_MAILER=log
LOG_LEVEL=error
EOF

# 5. Create data directories
mkdir -p data/{database,storage,cache,redis}
chmod -R 777 data

# 6. Start services
docker compose pull
docker compose up -d

# 7. Run migrations
docker compose exec ai-support-bot php artisan migrate --force

# 8. Check status
docker compose ps
docker compose logs -f
```

## 🔍 Managing the Deployment

### View Logs
```bash
cd /projects/ai-support-bot

# All services
docker compose logs -f

# Specific service
docker compose logs -f ai-support-bot
docker compose logs -f redis

# Last 50 lines
docker compose logs --tail=50 ai-support-bot
```

### Restart Services
```bash
cd /projects/ai-support-bot

# Restart all
docker compose restart

# Restart specific service
docker compose restart ai-support-bot
docker compose restart redis
```

### Stop/Start
```bash
cd /projects/ai-support-bot

# Stop all services
docker compose stop

# Start all services
docker compose start

# Stop and remove containers (data persists!)
docker compose down

# Start from scratch
docker compose up -d
```

### Update Environment Variables
```bash
cd /projects/ai-support-bot

# Edit .env file
nano .env

# Restart to apply changes
docker compose up -d
```

### Run Artisan Commands
```bash
cd /projects/ai-support-bot

# Run any artisan command
docker compose exec ai-support-bot php artisan migrate
docker compose exec ai-support-bot php artisan cache:clear
docker compose exec ai-support-bot php artisan config:clear
docker compose exec ai-support-bot php artisan queue:work
```

### Access Container Shell
```bash
cd /projects/ai-support-bot

# Access app container
docker compose exec ai-support-bot bash

# Access Redis CLI
docker compose exec redis redis-cli
```

## 💾 Backup & Restore

### Backup
```bash
cd /projects/ai-support-bot

# Backup all data
tar -czf ~/backup-$(date +%Y%m%d).tar.gz data/

# Backup only database
cp data/database/database.sqlite ~/database-backup-$(date +%Y%m%d).sqlite
```

### Restore
```bash
cd /projects/ai-support-bot

# Stop services
docker compose stop

# Restore from backup
tar -xzf ~/backup-20250117.tar.gz

# Start services
docker compose start
```

## 🔧 Troubleshooting

### Container won't start
```bash
cd /projects/ai-support-bot

# Check logs
docker compose logs ai-support-bot

# Check if ports are available
sudo netstat -tulpn | grep :80

# Recreate containers
docker compose down
docker compose up -d
```

### Database errors
```bash
cd /projects/ai-support-bot

# Check if database file exists
ls -la data/database/

# Recreate database
rm data/database/database.sqlite
docker compose exec ai-support-bot php artisan migrate --force
```

### Redis connection issues
```bash
cd /projects/ai-support-bot

# Test Redis
docker compose exec redis redis-cli ping

# Check Redis logs
docker compose logs redis

# Restart Redis
docker compose restart redis
```

### Permission errors
```bash
cd /projects/ai-support-bot

# Fix data directory permissions
chmod -R 777 data/

# Restart services
docker compose restart
```

## 🎨 Customizing Production Config

To modify production settings:

1. **Edit** `compose.override.production.yml` in your repository
2. **Commit and push** changes
3. **Create new release** → auto-deployment will use new config

Example - Change Redis password:

```yaml
# compose.override.production.yml
services:
  redis:
    command: redis-server --appendonly yes --requirepass YOUR_PASSWORD

  ai-support-bot:
    environment:
      - REDIS_PASSWORD=YOUR_PASSWORD
```

## 📊 Monitoring

### Check Service Health
```bash
cd /projects/ai-support-bot

# Service status
docker compose ps

# Healthcheck status
docker inspect ai-support-bot | grep -A 10 Health
```

### Resource Usage
```bash
# CPU and Memory usage
docker stats

# Disk usage
docker system df

# Clean up unused data
docker system prune -a
```

## 🆘 Emergency Procedures

### Rollback to Previous Version
```bash
cd /projects/ai-support-bot

# Update .env with previous image tag
nano .env  # Change DOCKER_IMAGE to previous tag

# Redeploy
docker compose pull
docker compose up -d
```

### Complete Reset
```bash
cd /projects/ai-support-bot

# DANGER: This deletes all data!
docker compose down -v
rm -rf data/
mkdir -p data/{database,storage,cache,redis}
chmod -R 777 data/
docker compose up -d
docker compose exec ai-support-bot php artisan migrate --force
```

## 📚 Additional Resources

- Docker Compose Documentation: https://docs.docker.com/compose/
- Laravel Deployment: https://laravel.com/docs/deployment
- GitHub Actions: https://docs.github.com/en/actions
