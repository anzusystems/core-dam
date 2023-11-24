# Local developement

### Setup image fallback

* enable fallback: set `CMS_IMAGE_FALLBACK_ENABLED=1` in `.env.local`
* specify fallback bucket: set `GCS_IMAGE_FALLBACK_STORAGE_BUCKET` (default fallback bucket `anzu-dam-image-production-bel`)
  * devel: `anzu-dam-image-devel-bel`
  * stage: `anzu-dam-image-stage-bel`
* setup google bucket credentials: create json credentials at path specified in env `GOOGLE_FALLBACK_BUCKET_CREDENTIALS` (default path is: `/var/www/html/var/secure/google/fallback-storage-bucket-credentials.json`)
  * devel credentials: lastpass `Shared-PP-Anzu-Development\devel`.`DAM: bucket credentials [DEVEL]` 

### Update migrations (on devel/stage/*** env)
Updates `migration delta` and import new images

cmd: `bin/consle anzu:media-api:update`

# Full migration

### Run migrations

cmd: `bin/consle anzu:media-api:migrate`

#### Migration stages
* buildTable - iterates over `mediaapi.mediaapi_image` table and populates `dam_media_api_mig.dam_media_api_mig` table.
  When populating the target table, it will also create `keywords` and `auhtors`  in `core_dam`.
* migrateUsers - iterates over `dam_media_api_mig.dam_media_api_mig` and migrate users (fetching data from central)
* migrateImages - synchronously migrate `images`
* imagePostprocess - writes `mediaapi` id and file_path to asset custom metadata

### Refresh properties

cmd: `bin/consle anzu:asset:refresh-properties`