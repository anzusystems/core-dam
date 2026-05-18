Anzu Core DAM
=====

[[_TOC_]]

## Preconditions

- Start [Anzu Tools][anzu-tools-containers] docker containers

## Installation

### 1. Clone repository

    git clone git@ssh.dev.azure.com:v3/petitpress/Anzu/core-dam

### 3. Start docker containers

See [Docker projects - installation][docker-projects-installation] docu for installation instructions

### 3. Add local domain to hosts

Add this entry to your hosts:

    127.0.0.1   core-dam.sme.local
    127.0.0.1   image.smedata.local
    127.0.0.1   uimage.smedata.local
    127.0.0.1   audio.smedata.local
    127.0.0.1   document.smedata.local
    127.0.0.1   admin-image.smedata.local

- Linux/Mac location:

  `/etc/hosts`

- Windows location

  `C:\Windows\System32\drivers\etc\hosts`

[anzu-tools-containers]: https://dev.azure.com/petitpress/Anzu/_git/tools?anchor=installation
[docker-projects-installation]: https://dev.azure.com/petitpress/DevOps/_wiki/wikis/DevOps.wiki/1088/Docker-projects-installation

## TTS Cron Jobs (DAM Worker)

The following cron entries must be configured on the **DAM worker** container. Set env var `TTS_REPLACED_RETENTION_DAYS` (default: `7`).

```cron
# Hourly — cancel stuck TTS regen jobs older than 1 hour
0 * * * * /app/bin/console app:tts:cleanup-stuck-regen --older-than=1h >> /var/log/cron/tts-cleanup-stuck.log 2>&1

# Daily 03:00 — delete replaced AudioFiles older than retention window
0 3 * * * /app/bin/console app:tts:cleanup-replaced-audio-files --older-than=${TTS_REPLACED_RETENTION_DAYS:-7}d >> /var/log/cron/tts-cleanup-replaced.log 2>&1

# Daily 04:00 — report voice-family binding gaps to ops Slack
0 4 * * * /app/bin/console app:tts:report-family-binding-gaps --provider=elevenlabs --ext-system=cms-tts >> /var/log/cron/tts-binding-gaps.log 2>&1
```

Recommended crontab entry shape (Kubernetes CronJob or system crontab):

```yaml
# Example Kubernetes CronJob shape (adapt image/env as needed):
# schedule: "0 3 * * *"
# command: ["bin/console", "app:tts:cleanup-replaced-audio-files", "--older-than=$(TTS_REPLACED_RETENTION_DAYS)d"]
```
