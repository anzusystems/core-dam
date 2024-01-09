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
