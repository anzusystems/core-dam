**APIDOC**
------------------

[http://core-dam.sme.localhost/apidoc/pub](http://core-dam.sme.localhost/apidoc/pub)

**PublicExport**
------------------

The entity that defines the license to be exported, the order, and the turn on/off mechanism.

Values:
* `cms-web`
* `cms-mobile`

**Listing and episode pagination**
------------------

Response list data format
```typescript
interface List {
  hasNextPage: boolean // indicates if next page is available
  data: object[],
  totalCount: 0 // deprecated field, always Zero
}

```

Pagination
```
?page=1&excludeIds[]=abcd&limit=20
```
* `page` default 1, available values 1 ... 100
* `excludeIds` default [], array of IDs, that is going to be excluded from response, you can define up to 50 ids
* `limit` default 20, available values are 10, 20 and 50

Thumbnail format, available title values are `small`, `medium`, `large` for Video and `small`, `medium` for Audio.
```typescript
interface Thumbnail {
    type: 'image'
    url: string,
    title: string,
    requestedWidth: number,
    requestedHeight: number
}
```

**VideoShow**
------------------

```typescript  
interface VideoShow {
    id: DocId
    title: string  
}
```  

**Get One**
```
GET /api/pub/{export-identifier}/video-shows/{videoShowId}
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/video-shows/1ef7423a-1a86-67ca-ab02-1ded068ce5f7](https://core-dam.smedevel.sk/api/pub/cms-web/video-shows/1ef7423a-1a86-67ca-ab02-1ded068ce5f7)


```json
{
  "id": "1edd902c-f393-6506-bee1-2bf75087e621",
  "title": "Rozhovory ZKH"
}
```

**Get List**

```
GET /api/pub/{export-identifier}/video-shows
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/video-shows](https://core-dam.smedevel.sk/api/pub/cms-web/video-shows)



**VideoShowEpisode**
------------------


```typescript
interface VideoShowEpisode {
    id: DocId
    title: string,
    asset: DocId,
    duration: number,
    publicationDate: string,
    thumbnail: null | Thumbnail[] // video image preview
}
```  

**Get one**
```
GET /api/pub/{export-identifier}/video-show-episodes/{videoShowEpisodeId}
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/video-show-episodes/1efbd502-40b9-6b68-a272-7bd52b164cbd](https://core-dam.smedevel.sk/api/pub/cms-web/video-show-episodes/1efbd502-40b9-6b68-a272-7bd52b164cbd)

```json
{
  "id": "1ef9dc91-6313-6378-895e-7ffaf32a8f70",
  "title": "Exprokurátor Harkabus: Bol som na štyroch výsluchoch. Keby sme toto robili my, kričali by, že sme gestapo",
  "asset": "1ef9c3aa-d93c-6158-82ab-79235fa09206",
  "thumbnail": {
    "links": [
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w2000-h0-c0/1ef9dc8d-3e7a-690c-af98-4f6a2299b1d7.jpg",
        "requestedWidth": 2000,
        "requestedHeight": 0,
        "title": "large"
      },
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w614-h345-c0/1ef9dc8d-3e7a-690c-af98-4f6a2299b1d7.jpg",
        "requestedWidth": 614,
        "requestedHeight": 345,
        "title": "small"
      },
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w1000-h563-c0/1ef9dc8d-3e7a-690c-af98-4f6a2299b1d7.jpg",
        "requestedWidth": 1000,
        "requestedHeight": 563,
        "title": "medium"
      }
    ]
  },
  "duration": 2713,
  "publicationDate": "2024-11-08T12:00:45.000000Z"
}
```

**Get List**
```
GET /api/pub/{export-identifier}/video-shows/{videoShowId}/video-show-episodes
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/video-shows/1edd902c-f393-6506-bee1-2bf75087e621/video-show-episodes](https://core-dam.smedevel.sk/api/pub/cms-web/video-shows/1edd902c-f393-6506-bee1-2bf75087e621/video-show-episodes)


**Podcast**
------------------

```typescript
interface Podcast {
    id: DocId
    title: string,
    description: string,
    rssUrl: string,
    thumbnail: null | Thumbnail[]
}
```  

**Get One**
```
GET /api/pub/{export-identifier}/podcasts/{podcastId}
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/podcasts/1edc17e7-d704-628c-8d2d-3126b6164b0b](https://core-dam.smedevel.sk/api/pub/cms-web/podcasts/1edc17e7-d704-628c-8d2d-3126b6164b0b)

```json
{
  "id": "1edc17e7-d704-628c-8d2d-3126b6164b0b",
  "title": "Dobré ráno",
  "description": "Denný podcast SME.sk, v ktorom každé ráno v skratke zhrnieme najdôležitejšie udalosti dňa. Redaktori SME sa okrem toho každý pracovný deň venujú jednej zaujímavej téme z oblasti politiky, ekonomiky či zdravia.",
  "rssUrl": "https://anchor.fm/s/8a651488/podcast/rss",
  "thumbnail": {
    "links": [
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w200-h200-c0/1edd98e4-6f6f-6656-ae4a-8d8483edb310.jpg",
        "requestedWidth": 200,
        "requestedHeight": 200,
        "title": "small"
      },
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w300-h300-c0/1edd98e4-6f6f-6656-ae4a-8d8483edb310.jpg",
        "requestedWidth": 300,
        "requestedHeight": 300,
        "title": "medium"
      }
    ]
  }
}

```

**Get List**
```
GET /api/pub/{export-identifier}/podcasts
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/podcasts](https://core-dam.smedevel.sk/api/pub/cms-web/podcasts)

**PodcastEpisode**
------------------

```typescript
interface PodcastEpisode {
    id: DocId
    title: string,
    description: string,
    rssUrl: string,
    thumbnail: null | Thumbnail[]
}
```

**Get One**
```
GET /api/pub/{export-identifier}/podcast-episodes/{podcastEpisodeId}
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/podcast-episodes/1efdd1bb-b04a-651a-81e0-b7fd5dddfa82](https://core-dam.smedevel.sk/api/pub/cms-web/podcast-episodes/1efdd1bb-b04a-651a-81e0-b7fd5dddfa82)

```json
{
  "id": "1efdd1bb-b04a-651a-81e0-b7fd5dddfa82",
  "title": "Fico má istých len 72 hlasov, čas do schôdze sa kráti (28. 1. 2025)",
  "description": "Robert Fico prstom ukazuje na Andreja Danka či Matúša Šutaja Eštoka a tvrdí: „Ak táto vláda padne, s dôverou sa obráťte na SNS a Hlas.“\nČas do februárovej schôdze sa kráti, v parlamente však rebeluje sedem poslancov z pôvodnej sedemdesiatdeviatky. Podčiarknuté a spočítané, predsedu vlády v parlamente s istotou podporí len 72 hlasov.\nA o tom sa s redaktorom domáceho spravodajstva Michalom Katuškom, ktorý koaličnú krízu sleduje, rozpráva v podcaste Dobré ráno Jana Krescanko Dibáková.\nZdroj zvukov: TA3, Denník N\nOdporúčanie:\nHviezdoslavove Krvavé sonety v excelentnom viacjazyčnom podaní. Šokujúce prvky na scéne a výborné výkony herečiek. Ak máte chuť vidieť Hviezdoslava, ako ste ho určite ešte nikdy nevideli, zamierte práve do bratislavského Divadla Pavla Országa Hviezdoslava. Pochmúrno ideálne sa hodiace k dnešnej dobe.\n–\nVšetky podcasty denníka SME nájdete⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠⁠",
  "duration": 0,
  "publicationDate": "2025-01-28T01:30:00.000000Z",
  "asset": "1efdd1bb-b040-6fd8-add0-b7fd5dddfa82",
  "thumbnail": {
    "links": [
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w200-h200-c0/1edd98e4-6f6f-6656-ae4a-8d8483edb310.jpg",
        "requestedWidth": 200,
        "requestedHeight": 200,
        "title": "small"
      },
      {
        "type": "image",
        "url": "https://image.smedatadevel.sk/image/w300-h300-c0/1edd98e4-6f6f-6656-ae4a-8d8483edb310.jpg",
        "requestedWidth": 300,
        "requestedHeight": 300,
        "title": "medium"
      }
    ]
  }
}
```

**Get List by podcast**
```
GET /api/pub/{export-identifier}/podcasts/{podcastId}/podcast-episodes
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/podcasts/1edc17e7-d704-628c-8d2d-3126b6164b0b/podcast-episodes](https://core-dam.smedevel.sk/api/pub/cms-web/podcasts/1edc17e7-d704-628c-8d2d-3126b6164b0b/podcast-episodes)

**Get List ordered by publicationDate**
```
GET /api/pub/{export-identifier}/podcast-episodes
```

Example: [https://core-dam.smedevel.sk/api/pub/cms-web/podcast-episodes](https://core-dam.smedevel.sk/api/pub/cms-web/podcast-episodes)

**Asset (Audio)**
------------------

```typescript
interface AudioMedia {
    type: string
    linkUrl: string|null // playable url link
    duration: number|null
    mediaUrl: string|null // link to extPage (toldo fe)
}
interface Podcast {
    episode: DocId
    id: DocId
    title: string
    description: string
    episodeTitle: string
    episodeDescription: string
    publicationDate: string,
}

interface Asset {
    id: DocId
    title: string
    assetType: 'audio'
    media: AudioMedia[] // non-empty array
    podcast: Podcast|null // audio must be playable without podcast 
    thumbnail: null|Thumbnail[] // audio without podcast has no thumbnail
    sibling: Asset|null
}
```

```
GET /api/pub/{export-identifier}/assets/{assetId}
```

get one (free with premium) (premium audio media is filtered if header `X-GW-Access-Content: 1` is missing)

Example: https://core-dam.smedevel.sk/api/pub/cms-web/assets/0792ea0f-72de-4597-a506-7ee2708880a9

Get one (bonus)

Example: https://core-dam.smedevel.sk/api/pub/cms-web/assets/1ef7ae85-be55-6e2a-a6f2-534fb46816a1

Get one (with sibling)

Example: https://core-dam.smedevel.sk/api/pub/cms-web/assets/1efdc070-8ee8-622a-a1fe-33f74a34980f


**Asset (Video)**
------------------

```typescript
interface Distribution {
    id: string
    type: string
    fallbackUrl: string
}

interface YoutubeDistribution extends Distribution {
    type: 'youtube'
}

interface JwDistribution extends Distribution {
    directUrl: string
    type: 'jwVideo'
}

interface VideoShow {
    episode: DocId
    id: DocId
    title: string
    episodeTitle: string
    publicationDate: string
}

interface Asset {
    id: DocId
    title: string
    assetType: 'video'
    duration: number
    distributions: Distribution[] // non-empty array
    videoSHow: VideoShow|null // video must be playable without podcast 
    thumbnail: null|Thumbnail[]
    sibling: Asset|null
}
```

Get one (with jw/yt distributions)

Example: https://core-dam.smedevel.sk/api/pub/cms-web/assets/1efdc070-8ee8-622a-a1fe-33f74a34980f

Get one (with sibling and videoShow)

Example: https://core-dam.smedevel.sk/api/pub/cms-web/assets/1efbc81d-b45d-6458-8e8d-0d4c7795563b