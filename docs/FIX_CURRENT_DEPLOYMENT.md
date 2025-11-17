# Fix Current Deployment - Database Issue

Your container is running but the SQLite database doesn't exist. Here's how to fix it **right now** without redeploying.

## Quick Fix (Run on Raspberry Pi)

SSH into your Raspberry Pi and run these commands:

```bash
# 1. Stop and remove current container
docker stop ai-support-bot
docker rm ai-support-bot

# 2. Create persistent data directories
mkdir -p /home/github/ai-support-bot-data/database
mkdir -p /home/github/ai-support-bot-data/storage
mkdir -p /home/github/ai-support-bot-data/cache
chmod -R 777 /home/github/ai-support-bot-data

# 3. Run container with volumes (replace YOUR_IMAGE with your actual image)
docker run -d \
  --name ai-support-bot \
  --restart unless-stopped \
  -p 8060:80 \
  -v /home/github/ai-support-bot-data/database:/var/www/html/database \
  -v /home/github/ai-support-bot-data/storage:/var/www/html/storage \
  -v /home/github/ai-support-bot-data/cache:/var/www/html/bootstrap/cache \
  -e APP_NAME="AI Support Bot" \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_KEY="YOUR_APP_KEY_HERE" \
  -e APP_URL="http://your-pi-ip:8060" \
  -e APP_PORT=80 \
  -e DB_CONNECTION=sqlite \
  -e DB_DATABASE=/var/www/html/database/database.sqlite \
  -e LOG_CHANNEL=stack \
  -e LOG_LEVEL=error \
  -e SESSION_DRIVER=file \
  -e CACHE_STORE=file \
  -e QUEUE_CONNECTION=sync \
  -e GEMINI_API_KEY="YOUR_GEMINI_KEY" \
  -e GEMINI_MODEL=gemini-2.5-flash \
  -e TELEGRAM_TOKEN="YOUR_TELEGRAM_TOKEN" \
  -e VITE_TELEGRAM_BOT_USERNAME="saslkdjxn_bot" \
  -e TELEGRAPH_BOT_NAME="saslkdjxn_bot" \
  -e TELEGRAPH_TOKEN="YOUR_TELEGRAM_TOKEN" \
  -e TELEGRAPH_WEBHOOK_URL="YOUR_WEBHOOK_URL" \
  -e MAIL_MAILER=log \
  sergeyphpdevua/ai-support-bot:d0dd4e36ac3a3811afa819739dc2b6bbf6fa30fd

# 4. Wait for container to start
sleep 5

# 5. Run database migrations
docker exec ai-support-bot php artisan migrate --force

# 6. Check logs
docker logs ai-support-bot
```

## Or Use This Simpler Version

If you have all environment variables set in GitHub, just run:

```bash
# Stop current container
docker stop ai-support-bot && docker rm ai-support-bot

# Create data directories
mkdir -p /home/github/ai-support-bot-data/{database,storage,cache}
chmod -R 777 /home/github/ai-support-bot-data

# Note: To make this work now, you'll need to manually add all -e flags
# OR commit the workflow changes and create a new release
```

## Verify It Works

```bash
# Check container is running
docker ps

# Check database was created
ls -la /home/github/ai-support-bot-data/database/
# Should see: database.sqlite

# Check logs for any errors
docker logs ai-support-bot 2>&1 | tail -20

# Test the application
curl http://localhost:8060
```

## What Changed?

### Before (Data Lost on Redeploy):
```
Container
├── /var/www/html/database/database.sqlite ❌ (deleted on redeploy)
├── /var/www/html/storage ❌ (deleted on redeploy)
```

### After (Data Persists):
```
Raspberry Pi: /home/github/ai-support-bot-data/
├── database/database.sqlite ✅ (persistent)
├── storage/ ✅ (persistent)
└── cache/ ✅ (persistent)
       ↓ mounted into ↓
Container: /var/www/html/
├── database/ → (volume from Pi)
├── storage/ → (volume from Pi)
└── bootstrap/cache/ → (volume from Pi)
```

## Benefits of This Approach

✅ **Database persists** across deployments
✅ **User data safe** when container restarts
✅ **Logs preserved** in storage directory
✅ **Sessions maintained** across deployments
✅ **Easy backups**: Just backup `/home/github/ai-support-bot-data/`

## Next Deployment

After you commit the updated workflow, future deployments will:
1. Automatically create the data directories
2. Mount them as volumes
3. Run migrations
4. Everything just works! ✨

## Backup Your Data

To backup your database:

```bash
# Create backup
cp /home/github/ai-support-bot-data/database/database.sqlite \
   /home/github/ai-support-bot-backup-$(date +%Y%m%d).sqlite

# Or backup everything
tar -czf /home/github/ai-support-bot-backup-$(date +%Y%m%d).tar.gz \
  /home/github/ai-support-bot-data/
```

## Restore from Backup

```bash
# Stop container
docker stop ai-support-bot

# Restore database
cp /home/github/ai-support-bot-backup-20250117.sqlite \
   /home/github/ai-support-bot-data/database/database.sqlite

# Start container
docker start ai-support-bot
```
