# Security Policy

## Supported Versions

We release patches for security vulnerabilities in the following versions:

| Version | Supported          | Notes                           |
| ------- | ------------------ | ------------------------------- |
| 2.x.x   | :white_check_mark: | Latest stable release           |
| 1.5.x   | :white_check_mark: | LTS - Security fixes only       |
| 1.4.x   | :x:                | End of life                     |
| 1.3.x   | :x:                | End of life                     |
| 1.2.x   | :x:                | End of life                     |
| 1.1.x   | :x:                | End of life                     |
| 1.0.x   | :x:                | End of life                     |
| < 1.0   | :x:                | Beta/Alpha - Not supported      |

**Legend:**
- :white_check_mark: = Security patches and bug fixes provided
- :x: = No longer supported, please upgrade

**Support Policy:**
- **Latest Major Version (2.x)**: Full support including features, bug fixes, and security patches
- **LTS Version (1.5.x)**: Long-term support with critical security fixes until 2026
- **Older Versions**: No support - users are encouraged to upgrade to a supported version

## Reporting a Vulnerability

We take the security of UniSoul seriously. If you believe you have found a security vulnerability, please report it to us as described below.

### Please Do Not

- **Do not** open a public GitHub issue for security vulnerabilities
- **Do not** publicly disclose the vulnerability before it has been addressed

### Please Do

1. **Report privately** by using one of these methods:
    - Open a [Security Advisory](https://github.com/BorschCode/UniSoul/security/advisories/new) (preferred)
    - Email the maintainers directly (check the repository for contact information)

2. **Include the following information** in your report:
    - Type of vulnerability (e.g., SQL injection, XSS, authentication bypass)
    - Full paths of source file(s) related to the vulnerability
    - Location of the affected source code (tag/branch/commit or direct URL)
    - Step-by-step instructions to reproduce the issue
    - Proof-of-concept or exploit code (if possible)
    - Impact of the vulnerability and potential attack scenarios

3. **Allow time for response**:
    - We will acknowledge receipt of your report within 48 hours
    - We will provide an estimated timeline for a fix within 7 days
    - We will notify you when the vulnerability is fixed

### What to Expect

- **Confirmation**: We'll confirm receipt of your vulnerability report
- **Updates**: We'll keep you informed about our progress
- **Credit**: We'll publicly credit you for the discovery (unless you prefer to remain anonymous)
- **Fix**: We'll work on a fix and coordinate the disclosure timeline with you

## Security Best Practices for Users

### Environment Variables

**Never commit sensitive data to the repository:**
- Keep `.env` file out of version control (already in `.gitignore`)
- Never commit API keys, passwords, or tokens
- Use GitHub Secrets for CI/CD sensitive data

### Database Security

- Use strong, unique passwords for database users
- Limit database user permissions (principle of least privilege)
- Use SSL/TLS connections to the database when possible
- Regularly backup your database

### Telegram Bot Security

- **OWNER_ID**: Set this to your Telegram user ID to receive important notifications
- Keep your `TELEGRAM_TOKEN` secret and never share it publicly
- Regularly rotate your bot token if you suspect it may be compromised
- Use Telegram's built-in bot security features

### API Keys

- **GEMINI_API_KEY**: Store securely in environment variables
- Rotate API keys regularly
- Monitor API usage for unusual activity
- Set up usage quotas and alerts

### Docker Security

- Keep your Docker images up to date
- Don't run containers as root when possible (UniSoul uses `www-data` user)
- Regularly scan images for vulnerabilities
- Use official base images (Ubuntu 24.04 in our case)

### Laravel Security

- Keep Laravel and all dependencies up to date
- Run `composer audit` regularly to check for vulnerable dependencies
- Use HTTPS in production (set `APP_URL` to https://)
- Set `APP_DEBUG=false` in production
- Enable CSRF protection (enabled by default)
- Use parameterized queries (Eloquent does this automatically)

### Redis Security

- Set a strong `REDIS_PASSWORD`
- Bind Redis to localhost or use firewall rules
- Disable dangerous commands in production
- Keep Redis updated

### Server Security

- Keep your operating system updated
- Use a firewall (e.g., UFW on Linux)
- Use SSH keys instead of passwords
- Disable root SSH login
- Keep PHP and all extensions updated
- Monitor logs for suspicious activity

### Regular Maintenance

```bash
# Check for vulnerable dependencies
vendor/bin/sail composer audit

# Update dependencies
vendor/bin/sail composer update

# Run Laravel's built-in security checks
vendor/bin/sail artisan config:cache
vendor/bin/sail artisan route:cache
vendor/bin/sail artisan view:cache
```

## Security Updates

Security updates will be released as soon as possible after a vulnerability is confirmed. Updates will be:

1. Released as a new version
2. Documented in the CHANGELOG
3. Announced in the GitHub Security Advisories
4. Tagged with the severity level (low, medium, high, critical)

## Security Features in UniSoul

- **Authentication & Authorization**: Laravel Sanctum for API authentication
- **Input Validation**: Laravel Form Requests for all user input
- **SQL Injection Protection**: Eloquent ORM with parameter binding
- **XSS Protection**: Blade template engine auto-escapes output
- **CSRF Protection**: Laravel's built-in CSRF protection
- **Rate Limiting**: Configurable rate limiting on routes
- **Environment Isolation**: Separate development and production configurations
- **Secure Session Management**: Database-backed sessions with encryption
- **Owner Verification**: Maintenance mode accessible only to OWNER_ID

## Compliance

This project follows:
- OWASP Top 10 security guidelines
- Laravel security best practices
- Secure coding standards for PHP
- Docker security best practices

## Questions?

If you have questions about security in UniSoul that are not sensitive in nature, feel free to:
- Open a public GitHub issue
- Start a discussion in GitHub Discussions
- Check the documentation

---

**Thank you for helping keep UniSoul secure!**
