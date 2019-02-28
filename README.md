# Hoarder

Hoarding from social media sites

## Install

Copy contents of .env.example into .env and update with local settings

`DB_DATABASE:` Your database name

`- DB_USERNAME:` Database user name

`- DB_PASSWORD:` Database password

`- DOWNLOAD_PATH:` Literal path to downloads directory

```bash
$ composer install
```

```bash
$ php artisan key:generate
```

```bash
$ php artisan migrate:fresh
```

## Basic Usage

Scrape watched subs

```bash
php artisan process:subs
```

Extract downloadable information from posts

```bash
php artisan process:posts
```

Additional processing to extract downloadable items.
This might be image albums or fetching direct links to videos

```bash
php artisan process:external
```

Download and save media items

```bash
php artisan process:downloads
```
