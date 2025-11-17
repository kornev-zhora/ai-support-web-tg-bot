# 🚀 Quick Deployment Guide

Get your AI Support Bot deployed to Raspberry Pi in 5 minutes!

## ✅ Prerequisites Checklist

- [ ] Raspberry Pi with Docker installed
- [ ] GitHub Actions runner configured on Pi
- [ ] Docker Hub account
- [ ] Telegram Bot Token (from @BotFather)
- [ ] Google Gemini API Key (from https://ai.google.dev/)

## 📝 Step-by-Step Setup

### 1. Generate Laravel APP_KEY

Run this command locally or on any machine with Docker:

```bash
docker run --rm borschcode/ai-support-bot:latest php artisan key:generate --show
```

**Copy the output** (looks like: `base64:xxxxx...`)

### 2. Configure GitHub Environment

Go to: https://github.com/BorschCode/ai-support-web-tg-bot/settings/environments/10014763073/edit

#### Add 3 Secrets (🔒):

| Name | Where to get it |
|------|-----------------|
| `APP_KEY` | From step 1 above |
| `GEMINI_API_KEY` | https://ai.google.dev/ → Get API Key |
| `TELEGRAM_TOKEN` | Telegram → @BotFather → /newbot |

**How**: Click "Add secret" → Enter name → Paste value → "Add secret"

#### Add 3 Variables (📝):

| Name | Example |
|------|---------|
| `TELEGRAM_BOT_USERNAME` | `saslkdjxn_bot` (your bot username, no @) |
| `APP_URL` | `http://192.168.1.100` (your Raspberry Pi IP) |
| `TELEGRAPH_WEBHOOK_URL` | `http://192.168.1.100/webhook` |

**How**: Click "Add variable" → Enter name → Enter value → "Add variable"

### 3. Commit Changes

```bash
git add .
git commit -m "feat: configure GitHub Environment deployment"
git push
```

### 4. Create Release

1. Go to: https://github.com/BorschCode/ai-support-web-tg-bot/releases
2. Click **"Draft a new release"**
3. Click **"Choose a tag"** → Type `v1.0.0` → Click "Create new tag"
4. Click **"Publish release"**

### 5. Watch Deployment

1. Go to: https://github.com/BorschCode/ai-support-web-tg-bot/actions
2. Watch the workflows run:
   - ✅ Docker Build & Deploy (builds image)
   - ✅ Deploy to Raspberry Pi (deploys to your Pi)

### 6. Verify It Works

On your Raspberry Pi:

```bash
# Check container is running
docker ps

# View logs
docker logs -f ai-support-bot

# Test the bot
curl http://localhost
```

On Telegram:
1. Open Telegram
2. Search for your bot: `@saslkdjxn_bot`
3. Send `/start`
4. Bot should respond!

## 🎯 That's It!

Your bot is now deployed and will auto-update whenever you create a new release!

## 📚 Detailed Guides

- **Environment Setup**: See `GITHUB_ENVIRONMENT_SETUP.md`
- **Raspberry Pi Setup**: See `RASPBERRY_PI_SETUP.md`
- **Troubleshooting**: See sections below

## 🔧 Common Issues

### Bot not responding

```bash
# Check logs for errors
docker logs ai-support-bot 2>&1 | grep -i error

# Verify environment variables are loaded
docker inspect ai-support-bot | grep -i telegram_token
```

### Build failed

- Check: https://github.com/BorschCode/ai-support-web-tg-bot/actions
- Look for red ❌ in the workflow
- Click to see detailed error logs

### Deployment failed

Check Raspberry Pi runner:
```bash
cd ~/actions-runner
./run.sh  # Should show "Listening for Jobs"
```

## 🔄 Updating Your Bot

To deploy updates:

1. Make code changes
2. Commit and push to GitHub
3. Create a new release (e.g., `v1.0.1`)
4. Wait 5 minutes
5. New version is live! 🎉

## 🆘 Need Help?

1. Check container logs: `docker logs ai-support-bot`
2. Check GitHub Actions logs: Repository → Actions
3. Open an issue: https://github.com/BorschCode/ai-support-web-tg-bot/issues
