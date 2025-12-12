# AssetCmsFactory Usage

The `AssetCmsFactory` class is responsible for transforming `Asset` entities into `AssetCmsSysDto` objects for CMS system integration.

## Overview

The factory provides a `create` method that accepts an `Asset` entity and returns a populated `AssetCmsSysDto` instance with the following mapped properties:

- `assetId` - The Asset's unique identifier
- `title` - Extracted from asset metadata custom data
- `description` - Extracted from asset metadata custom data  
- `seriesName` - Extracted from asset metadata custom data
- `publishedAt` - Extracted from asset metadata custom data if available
- `duration` - For audio/video assets, extracted from file attributes
- `playable` - Boolean indicating if the asset is playable (audio/video)
- `mediaUrl` - URL for accessing the media (implementation dependent)
- `imageFileId` - ID of associated image file (for image assets or thumbnails)

## Usage Example

```php
use App\Domain\Asset\AssetCmsFactory;
use AnzuSystems\CoreDamBundle\Entity\Asset;

// Inject the factory (typically via dependency injection)
$factory = new AssetCmsFactory();

// Transform an Asset entity to AssetCmsSysDto
$asset = // ... get your Asset entity
$dto = $factory->create($asset);

// Access the transformed data
echo $dto->getTitle();
echo $dto->getDescription();
echo $dto->getDuration();
echo $dto->isPlayable() ? 'Yes' : 'No';
```

## Asset Type Handling

The factory handles different asset types appropriately:

### Audio Assets
- Sets `duration` from audio file attributes
- Sets `playable` to `true`
- Attempts to resolve `mediaUrl` (implementation dependent)

### Video Assets  
- Sets `duration` from video file attributes
- Sets `playable` to `true`
- Attempts to resolve `mediaUrl` (implementation dependent)

### Image Assets
- Sets `duration` to 0
- Sets `playable` to `false`
- Sets `imageFileId` to the image file's ID
- Sets `mediaUrl` to `null`

### Document Assets
- Sets `duration` to 0
- Sets `playable` to `false`
- Sets `mediaUrl` to `null`

## Metadata Mapping

The factory extracts data from the Asset's metadata custom data array:

```php
$customData = $asset->getMetadata()->getCustomData();
$dto->setTitle($customData['title'] ?? '');
$dto->setDescription($customData['description'] ?? '');
$dto->setSeriesName($customData['seriesName'] ?? '');
```

## Extension Points

The factory includes placeholder methods for resolving media URLs:

- `getAudioMediaUrl(AudioFile $audioFile): ?string`
- `getVideoMediaUrl(VideoFile $videoFile): ?string`

These methods currently return `null` but can be extended to integrate with your specific distribution systems (JW Player, YouTube, etc.) by injecting additional services.

## Testing

The factory includes comprehensive unit tests covering:

- Basic asset data transformation
- Audio asset specific handling
- Image asset handling with file ID mapping
- Published date handling
- Different asset type scenarios

Run tests with:
```bash
php vendor/bin/phpunit tests/Domain/Asset/AssetCmsFactoryTest.php
```
