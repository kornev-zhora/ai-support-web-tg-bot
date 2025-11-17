# Raspberry Pi Deployment Setup Guide

This guide will help you set up your Raspberry Pi for automated Docker deployments from GitHub Actions.

## Prerequisites

- Raspberry Pi (any model - Pi 2, 3, 4, 5, or Zero)
  - **Pi 2**: ARMv7 (32-bit)
  - **Pi 3+**: ARM64 (64-bit) or ARMv7 (32-bit) depending on OS
- Ubuntu or Raspberry Pi OS installed
- Internet connection
- GitHub account with repository access

> **Note**: The Docker image is built for both ARMv7 and ARM64, so it works on all Raspberry Pi models.

## Step 1: Install Required Packages

SSH into your Raspberry Pi and run:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential tools
sudo apt install -y jq curl git

# Verify installations
jq --version
docker --version
git --version
```

## Step 2: Install Docker (if not already installed)

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add your user to docker group (replace 'pi' with your username)
sudo usermod -aG docker $USER

# Apply group changes (or logout and login again)
newgrp docker

# Test Docker without sudo
docker ps
docker run hello-world
```

## Step 3: Login to Docker Hub

```bash
# Login to Docker Hub (you'll need your Docker Hub credentials)
docker login

# Verify login
docker info | grep Username
```

## Step 4: Install GitHub Actions Runner

```bash
# Create a directory for the runner
mkdir -p ~/actions-runner && cd ~/actions-runner

# Check your architecture
uname -m
# Result: armv7l = 32-bit ARM (use ARM download)
# Result: aarch64 = 64-bit ARM (use ARM64 download)

# FOR RASPBERRY PI 2 / ARMv7 (32-bit):
curl -o actions-runner-linux-arm-2.329.0.tar.gz -L \
  https://github.com/actions/runner/releases/download/v2.329.0/actions-runner-linux-arm-2.329.0.tar.gz
tar xzf ./actions-runner-linux-arm-2.329.0.tar.gz
rm actions-runner-linux-arm-2.329.0.tar.gz

# OR FOR RASPBERRY PI 3+ / ARM64 (64-bit):
# curl -o actions-runner-linux-arm64-2.329.0.tar.gz -L \
#   https://github.com/actions/runner/releases/download/v2.329.0/actions-runner-linux-arm64-2.329.0.tar.gz
# tar xzf ./actions-runner-linux-arm64-2.329.0.tar.gz
# rm actions-runner-linux-arm64-2.329.0.tar.gz
```

## Step 5: Configure the Runner

Go to your GitHub repository settings:
1. Navigate to: `Settings` → `Actions` → `Runners` → `New self-hosted runner`
2. Select `Linux` and `ARM64`
3. Copy the configuration token from GitHub

Run the configuration:

```bash
# Configure the runner (replace TOKEN with your actual token)
./config.sh --url https://github.com/BorschCode/ai-support-web-tg-bot \
  --token YOUR_TOKEN_HERE \
  --name raspi-deploy \
  --labels raspi-deploy

# Press Enter for default work folder
```

## Step 6: Install Runner as a Service

```bash
# Install the runner as a systemd service
sudo ./svc.sh install

# Start the service
sudo ./svc.sh start

# Check status
sudo ./svc.sh status

# Enable auto-start on boot
sudo systemctl enable actions.runner.*
```

## Step 7: Configure Environment Variables (Optional)

Create a `.env` file for production environment variables:

```bash
# Create directory for app data
sudo mkdir -p /opt/ai-support-bot
cd /opt/ai-support-bot

# Create .env file (customize as needed)
sudo nano .env
```

Add your production environment variables:
```env
APP_NAME="AI Support Bot"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-raspberry-pi-ip

# Database configuration
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

# Add other required environment variables
```

## Step 8: Update Deployment Workflow (Optional)

If you want to use environment variables from a file, update the deploy workflow:

```yaml
# In .github/workflows/deploy-raspi.yml, modify the run command:
docker run -d \
  --name ai-support-bot \
  -p 80:80 \
  --env-file /opt/ai-support-bot/.env \
  $IMAGE_NAME:${{ env.IMAGE_TAG }}
```

## Verification

Check if everything is set up correctly:

```bash
# 1. Check if runner is active
cd ~/actions-runner
sudo ./svc.sh status

# 2. Check Docker
docker ps
docker images

# 3. Check if port 80 is available
sudo netstat -tulpn | grep :80

# 4. Verify jq is installed
which jq
```

## Troubleshooting

### Runner Not Starting
```bash
# Check logs
sudo journalctl -u actions.runner.* -f

# Restart service
sudo ./svc.sh restart
```

### Port 80 Already in Use
```bash
# Find what's using port 80
sudo lsof -i :80

# Kill the process or use a different port in deployment
```

### Docker Permission Denied
```bash
# Add user to docker group again
sudo usermod -aG docker $USER

# Logout and login, or run:
newgrp docker
```

### Docker Pull Fails
```bash
# Re-login to Docker Hub
docker logout
docker login
```

## Testing the Deployment

1. Create a new release on GitHub
2. Monitor the Actions tab for the build workflow
3. Once build completes, check the deployment on your Pi:

```bash
# Check running containers
docker ps

# Check logs
docker logs ai-support-bot

# Test the application
curl http://localhost
```

## Maintenance

### View Application Logs
```bash
docker logs -f ai-support-bot
```

### Restart Application
```bash
docker restart ai-support-bot
```

### Clean Up Old Images
```bash
docker image prune -a -f
```

### Update Runner
```bash
cd ~/actions-runner
sudo ./svc.sh stop
./config.sh remove
# Download and configure new version
```

## Security Recommendations

1. **Use SSH keys** instead of passwords
2. **Enable firewall**:
   ```bash
   sudo ufw allow 22    # SSH
   sudo ufw allow 80    # HTTP
   sudo ufw allow 443   # HTTPS (if needed)
   sudo ufw enable
   ```
3. **Keep system updated**: `sudo apt update && sudo apt upgrade -y`
4. **Use environment variables** for sensitive data, never commit them to git
5. **Consider using Docker secrets** for production credentials

## Next Steps

- Set up reverse proxy (nginx) for HTTPS
- Configure automatic backups
- Set up monitoring and alerts
- Configure log rotation
