# GitHub Environment Variables Setup

This guide shows you how to configure environment variables using GitHub Environments for deployment.

## Why GitHub Environments?

✅ **Advantages:**
- Centralized configuration in GitHub
- Works on any server (not just Raspberry Pi)
- Easy to update without SSH access
- Different configs for different environments (staging, production)
- Built-in protection rules and approvals

## Setup Instructions

### Step 1: Go to Environment Settings

Visit your repository's environment settings:
- **Direct link**: https://github.com/BorschCode/ai-support-web-tg-bot/settings/environments/10014763073/edit
- **Or navigate**: Repository → Settings → Environments → `raspberry`

### Step 2: Add Environment Variables

Click **"Add variable"** or **"Add secret"** and configure the following:

#### Required Secrets (🔒 Encrypted)

These contain sensitive data and will be hidden:

| Name | Value | Example |
|------|-------|---------|
| `APP_KEY` | Generate with `php artisan key:generate` | `base64:xxxxx...` |
| `GEMINI_API_KEY` | Your Google Gemini API key | `AIzaSy...` |
| `TELEGRAM_TOKEN` | Bot token from @BotFather | `123456:ABC-DEF...` |

**How to add a secret:**
1. Click **"Add secret"**
2. Enter the **Name** (e.g., `GEMINI_API_KEY`)
3. Enter the **Value** (your actual API key)
4. Click **"Add secret"**

#### Required Variables (📝 Public)

These are non-sensitive configuration values:

| Name | Value | Example |
|------|-------|---------|
| `TELEGRAM_BOT_USERNAME` | Your bot username (no @) | `saslkdjxn_bot` |
| `APP_URL` | Your Raspberry Pi URL/IP | `http://192.168.1.100` |
| `TELEGRAPH_WEBHOOK_URL` | Webhook URL for Telegram | `https://your-domain.com/webhook` |

**How to add a variable:**
1. Click **"Add variable"**
2. Enter the **Name** (e.g., `TELEGRAM_BOT_USERNAME`)
3. Enter the **Value** (e.g., `saslkdjxn_bot`)
4. Click **"Add variable"**

#### Optional Variables (with defaults)

Only add these if you want to override the defaults:

| Name | Default | Purpose |
|------|---------|---------|
| `APP_NAME` | `AI Support Bot` | Application name |
| `APP_PORT` | `80` | Port to expose on host |
| `GEMINI_MODEL` | `gemini-2.5-flash` | AI model to use |
| `DB_CONNECTION` | `sqlite` | Database driver |
| `DB_DATABASE` | `/var/www/html/database/database.sqlite` | Database path |
| `LOG_LEVEL` | `error` | Logging level |
| `SESSION_DRIVER` | `file` | Session storage |
| `CACHE_STORE` | `file` | Cache storage |
| `QUEUE_CONNECTION` | `sync` | Queue driver |
| `MAIL_MAILER` | `log` | Mail driver |

## Complete Setup Checklist

### 1️⃣ Secrets (Required)

- [ ] `APP_KEY` - Laravel application key
- [ ] `GEMINI_API_KEY` - Google Gemini API key
- [ ] `TELEGRAM_TOKEN` - Telegram bot token

### 2️⃣ Variables (Required)

- [ ] `TELEGRAM_BOT_USERNAME` - Bot username (e.g., `saslkdjxn_bot`)
- [ ] `APP_URL` - Your server URL (e.g., `http://192.168.1.100`)
- [ ] `TELEGRAPH_WEBHOOK_URL` - Webhook URL

### 3️⃣ Variables (Optional)

- [ ] `APP_PORT` - Only if you want a different port than 80
- [ ] `GEMINI_MODEL` - Only if you want a different model
- [ ] Other configuration as needed

## How to Generate APP_KEY

You need to generate a Laravel application key. Here are two methods:

### Method 1: Using Docker (Recommended)

```bash
# On your local machine or Raspberry Pi
docker run --rm YOUR_DOCKERHUB_USERNAME/ai-support-bot:latest \
  php artisan key:generate --show
```

Copy the output (e.g., `base64:xxxxx...`) and add it as the `APP_KEY` secret.

### Method 2: Using Local Laravel Installation

```bash
# If you have the project locally
php artisan key:generate --show
```

## Example Configuration

Here's a complete example setup:

### Secrets
```
APP_KEY = base64:random-generated-key-here
GEMINI_API_KEY = AIzaSyYourActualAPIKeyHere
TELEGRAM_TOKEN = 1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
```

### Variables
```
TELEGRAM_BOT_USERNAME = saslkdjxn_bot
APP_URL = http://192.168.1.100
TELEGRAPH_WEBHOOK_URL = https://your-domain.com/webhook
GEMINI_MODEL = gemini-2.5-flash
APP_PORT = 80
```

## Updating Variables

To update any variable or secret:

1. Go to the environment page: https://github.com/BorschCode/ai-support-web-tg-bot/settings/environments/10014763073/edit
2. Find the variable/secret you want to update
3. Click **"Update"** or the pencil icon
4. Change the value
5. Save
6. Create a new release to redeploy with new values

## Multiple Environments

You can create multiple environments (e.g., `staging`, `production`):

1. Go to: Repository → Settings → Environments
2. Click **"New environment"**
3. Name it (e.g., `staging`)
4. Configure different variables for each environment
5. Update workflow to use different environments

## Environment Protection Rules

You can add protection rules to the `raspberry` environment:

1. Go to environment settings
2. Enable **"Required reviewers"** - Require approval before deployment
3. Enable **"Wait timer"** - Add delay before deployment
4. Set **"Deployment branches"** - Only deploy from specific branches

## Verifying Your Setup

After adding all variables and secrets:

1. **Commit and push** any workflow changes
2. **Create a new release** on GitHub
3. **Monitor the deployment**:
   - Go to Actions tab
   - Watch the `Deploy to Raspberry Pi` job
4. **Check container logs**:
   ```bash
   docker logs ai-support-bot
   ```

## Troubleshooting

### Variable not found

**Error**: Container starts but variables are empty

**Solution**:
- Check variable name spelling in GitHub Environment
- Ensure you're in the correct environment (`raspberry`)
- Verify secrets are added as "secrets", not "variables"

### Cannot access secrets

**Error**: `secrets.SOMETHING` is empty in workflow

**Solution**:
- Secrets must be added in the Environment, not Repository secrets
- Go to: Settings → Environments → raspberry → Add secret

### Container fails to start

**Check logs**:
```bash
docker logs ai-support-bot 2>&1 | head -50
```

Common issues:
- Missing `APP_KEY` → Generate and add it
- Missing `TELEGRAM_TOKEN` → Add bot token from @BotFather
- Missing `GEMINI_API_KEY` → Add API key from Google AI Studio

## Resources

- [GitHub Environments Documentation](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment)
- [GitHub Secrets Documentation](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [Laravel Environment Configuration](https://laravel.com/docs/configuration)
- [Google Gemini API](https://ai.google.dev/)
- [Telegram Bot API](https://core.telegram.org/bots/api)
