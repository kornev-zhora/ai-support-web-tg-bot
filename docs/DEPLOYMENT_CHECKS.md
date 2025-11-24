# Deployment Health Checks

This guide helps you verify that all services are working correctly after deployment.

## Quick Health Check Script

Run this script to test all critical services:

```bash
./deployment-check.sh
```

## Manual Health Checks

### 1. Redis Connection Check

Redis is critical for caching messages. Test the connection:

```bash
# Inside the Docker container
docker exec -it ai-support-bot php artisan tinker
```

Then run:

```php
use Illuminate\Support\Facades\Redis;

// Test Redis connection
Redis::ping();
// Should return: true or "PONG"

// Test write/read
Redis::set('test_key', 'test_value');
Redis::get('test_key');
// Should return: "test_value"

// Clean up
Redis::del('test_key');
exit
```

### 2. Database Connection Check

```bash
docker exec -it ai-support-bot php artisan db:show
```

Expected output should show PostgreSQL connection details.

### 3. Cache System Check

```bash
docker exec -it ai-support-bot php artisan cache:clear
```

Should complete without errors.

### 4. Application Health

```bash
# Check if the app is running
curl -I http://localhost:8060

# Check logs for errors
docker exec -it ai-support-bot tail -n 50 storage/logs/laravel.log
```

## Common Issues

### Redis Connection Refused

**Error:** `Connection refused` when connecting to Redis

**Cause:** Your Laravel app in Docker is trying to connect to `redis` hostname, but Redis is running as a service on the host machine.

**Solutions:**

#### Option 1: Use Host Redis (Recommended for development)

Update your `.env` file:

```env
# Change from container name to host IP
REDIS_HOST=host.docker.internal
# Or use the Docker bridge IP
# REDIS_HOST=172.17.0.1
```

Restart the container:

```bash
docker compose restart ai-support-bot
```

Test the connection:

```bash
docker exec -it ai-support-bot php artisan tinker
```

```php
use Illuminate\Support\Facades\Redis;
Redis::ping();
```

#### Option 2: Use Docker Redis Container (Recommended for production)

Stop the host Redis service and use the containerized Redis:

```bash
# Stop host Redis
sudo systemctl stop redis-server
sudo systemctl disable redis-server

# Make sure Docker Redis is running
docker compose up -d redis

# Update .env (should already be set)
REDIS_HOST=redis
REDIS_PORT=6379
```

Restart the application:

```bash
docker compose restart ai-support-bot
```

#### Option 3: Configure Host Redis to Accept External Connections

If you want to keep using host Redis, configure it to accept connections from Docker:

1. Edit Redis configuration:

```bash
sudo nano /etc/redis/redis.conf
```

2. Find and modify these lines:

```conf
# Change from:
bind 127.0.0.1 ::1

# To (allow Docker bridge network):
bind 127.0.0.1 ::1 172.17.0.1
```

3. Restart Redis:

```bash
sudo systemctl restart redis-server
```

4. Update `.env`:

```env
REDIS_HOST=172.17.0.1
```

5. Restart container:

```bash
docker compose restart ai-support-bot
```

### Database Connection Issues

**Error:** Could not connect to database

**Check:**

```bash
# Verify PostgreSQL container is running
docker compose ps pgsql

# Check logs
docker compose logs pgsql

# Test connection from host
docker exec -it ai-support-bot-pgsql-1 psql -U sail -d laravel
```

### Permission Issues

**Error:** Permission denied on storage or cache

**Fix:**

```bash
# From host
docker exec -it ai-support-bot chmod -R 775 storage bootstrap/cache
docker exec -it ai-support-bot chown -R www-data:www-data storage bootstrap/cache
```

### Telegram Webhook Issues

**Error:** Telegram not receiving updates

**Check:**

1. Verify webhook is set:

```bash
docker exec -it ai-support-bot php artisan telegram:set-webhook
```

2. Check webhook info:

```bash
curl https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo
```

3. Test with a message to your bot

## Environment-Specific Configuration

### Development (.env)

```env
APP_ENV=local
APP_DEBUG=true
REDIS_HOST=host.docker.internal  # Use host Redis
LOG_LEVEL=debug
```

### Production (.env.production)

```env
APP_ENV=production
APP_DEBUG=false
REDIS_HOST=redis  # Use container Redis
LOG_LEVEL=error
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Health Check Endpoints

Add these to your monitoring:

- **Application**: `http://your-domain/`
- **Health Check**: Create a `/health` endpoint that checks:
  - Database connection
  - Redis connection
  - Disk space
  - Queue status

## Automated Monitoring

Consider setting up:

1. **Uptime monitoring** (UptimeRobot, Pingdom)
2. **Log monitoring** (Laravel Telescope, Sentry)
3. **Performance monitoring** (New Relic, DataDog)
4. **Error tracking** (Sentry, Bugsnag)

## Post-Deployment Checklist

- [ ] Redis connection working
- [ ] Database migrations applied
- [ ] Cache cleared and warmed
- [ ] Logs are clean (no errors)
- [ ] Telegram webhook set
- [ ] Test message to bot works
- [ ] Web interface loads
- [ ] AI responses working (check Gemini API)
- [ ] Assets compiled (no 404s)
- [ ] SSL certificate valid (production)
- [ ] Backups configured
- [ ] Monitoring alerts configured
