<?php

declare(strict_types=1);

namespace App\Security\Permission;

use AnzuSystems\CoreDamBundle\Security\Permission\DamPermissions as BaseDamPermissions;

final class DamPermissions extends BaseDamPermissions
{
    public const string GRANTS = 'grants';
    public const string UI = 'ui';
    public const string UI_CATEGORY = 'category';
    public const string UI_GROUP = 'group';

    // User
    public const string DAM_USER_CREATE = 'dam_user_create';
    public const string DAM_USER_UPDATE = 'dam_user_update';
    public const string DAM_USER_READ = 'dam_user_read';

    // PermissionGroup
    public const string DAM_PERMISSION_GROUP_CREATE = 'dam_permissionGroup_create';
    public const string DAM_PERMISSION_GROUP_UPDATE = 'dam_permissionGroup_update';
    public const string DAM_PERMISSION_GROUP_READ = 'dam_permissionGroup_view';
    public const string DAM_PERMISSION_GROUP_DELETE = 'dam_permissionGroup_delete';

    public static function allDetail(): array
    {
        return [
            // Asset
            BaseDamPermissions::DAM_ASSET_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET,
                ],
            ],
            BaseDamPermissions::DAM_ASSET_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET,
                ],
            ],
            BaseDamPermissions::DAM_ASSET_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET,
                ],
            ],
            BaseDamPermissions::DAM_ASSET_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET,
                ],
            ],
            // Video
            BaseDamPermissions::DAM_VIDEO_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_VIDEO,
                ],
            ],
            BaseDamPermissions::DAM_VIDEO_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_VIDEO,
                ],
            ],
            BaseDamPermissions::DAM_VIDEO_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_VIDEO,
                ],
            ],
            BaseDamPermissions::DAM_VIDEO_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_VIDEO,
                ],
            ],
            // Audio
            BaseDamPermissions::DAM_AUDIO_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUDIO,
                ],
            ],
            BaseDamPermissions::DAM_AUDIO_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUDIO,
                ],
            ],
            BaseDamPermissions::DAM_AUDIO_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUDIO,
                ],
            ],
            BaseDamPermissions::DAM_AUDIO_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUDIO,
                ],
            ],
            // CustomForm
            BaseDamPermissions::DAM_CUSTOM_FORM_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_CUSTOM_FORM,
                ],
            ],
            BaseDamPermissions::DAM_CUSTOM_FORM_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_CUSTOM_FORM,
                ],
            ],
            BaseDamPermissions::DAM_CUSTOM_FORM_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_CUSTOM_FORM,
                ],
            ],
            // Custom Form Element
            BaseDamPermissions::DAM_CUSTOM_FORM_ELEMENT_VIEW => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_CUSTOM_FORM_ELEMENT,
                ],
            ],
            // Document
            BaseDamPermissions::DAM_DOCUMENT_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DOCUMENT,
                ],
            ],
            BaseDamPermissions::DAM_DOCUMENT_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DOCUMENT,
                ],
            ],
            BaseDamPermissions::DAM_DOCUMENT_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DOCUMENT,
                ],
            ],
            BaseDamPermissions::DAM_DOCUMENT_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DOCUMENT,
                ],
            ],
            // Image
            BaseDamPermissions::DAM_IMAGE_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_IMAGE,
                ],
            ],
            BaseDamPermissions::DAM_IMAGE_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_IMAGE,
                ],
            ],
            BaseDamPermissions::DAM_IMAGE_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_IMAGE,
                ],
            ],
            BaseDamPermissions::DAM_IMAGE_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_IMAGE,
                ],
            ],
            // RegionOfInterest
            BaseDamPermissions::DAM_REGION_OF_INTEREST_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_REGION_OF_INTEREST,
                ],
            ],
            BaseDamPermissions::DAM_REGION_OF_INTEREST_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_REGION_OF_INTEREST,
                ],
            ],
            BaseDamPermissions::DAM_REGION_OF_INTEREST_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_REGION_OF_INTEREST,
                ],
            ],
            BaseDamPermissions::DAM_REGION_OF_INTEREST_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_REGION_OF_INTEREST,
                ],
            ],
            // User
            self::DAM_USER_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_USER,
                ],
            ],
            self::DAM_USER_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_USER,
                ],
            ],
            self::DAM_USER_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_USER,
                ],
            ],
            // PermissionGroup
            self::DAM_PERMISSION_GROUP_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PERMISSION_GROUP,
                ],
            ],
            self::DAM_PERMISSION_GROUP_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PERMISSION_GROUP,
                ],
            ],
            self::DAM_PERMISSION_GROUP_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PERMISSION_GROUP,
                ],
            ],
            self::DAM_PERMISSION_GROUP_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PERMISSION_GROUP,
                ],
            ],
            // AssetLicence
            self::DAM_ASSET_LICENCE_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET_LICENCE,
                ],
            ],
            self::DAM_ASSET_LICENCE_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET_LICENCE,
                ],
            ],
            self::DAM_ASSET_LICENCE_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET_LICENCE,
                ],
            ],
            self::DAM_ASSET_LICENCE_LIST => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET_LICENCE,
                ],
            ],
            // ExtSystem
            self::DAM_EXT_SYSTEM_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_EXT_SYSTEM,
                ],
            ],
            self::DAM_EXT_SYSTEM_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_EXT_SYSTEM,
                ],
            ],
            self::DAM_EXT_SYSTEM_LIST => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_EXT_SYSTEM,
                ],
            ],
            // Author
            self::DAM_AUTHOR_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PERMISSION_GROUP,
                ],
            ],
            self::DAM_AUTHOR_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUTHOR,
                ],
            ],
            self::DAM_AUTHOR_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUTHOR,
                ],
            ],
            self::DAM_AUTHOR_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_AUTHOR,
                ],
            ],
            // Keyword
            self::DAM_KEYWORD_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_KEYWORD,
                ],
            ],
            self::DAM_KEYWORD_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_KEYWORD,
                ],
            ],
            self::DAM_KEYWORD_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_KEYWORD,
                ],
            ],
            self::DAM_KEYWORD_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_KEYWORD,
                ],
            ],
            // Podcast
            self::DAM_PODCAST_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            self::DAM_PODCAST_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            self::DAM_PODCAST_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            self::DAM_PODCAST_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            // Podcast Episode
            self::DAM_PODCAST_EPISODE_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            self::DAM_PODCAST_EPISODE_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            self::DAM_PODCAST_EPISODE_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            self::DAM_PODCAST_EPISODE_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            // Distribution Category
            self::DAM_DISTRIBUTION_CATEGORY_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION_CATEGORY,
                ],
            ],
            self::DAM_DISTRIBUTION_CATEGORY_CREATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION_CATEGORY,
                ],
            ],
            self::DAM_DISTRIBUTION_CATEGORY_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION_CATEGORY,
                ],
            ],
            self::DAM_DISTRIBUTION_CATEGORY_DELETE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION_CATEGORY,
                ],
            ],
            // Distribution Category Select
            self::DAM_DISTRIBUTION_CATEGORY_SELECT_READ => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION_CATEGORY_SELECT,
                ],
            ],
            self::DAM_DISTRIBUTION_CATEGORY_SELECT_UPDATE => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION_CATEGORY_SELECT,
                ],
            ],
            // Distribution
            BaseDamPermissions::DAM_DISTRIBUTION_ACCESS => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_DISTRIBUTION,
                ],
            ],
            // Asset External Provider
            BaseDamPermissions::DAM_ASSET_EXTERNAL_PROVIDER_ACCESS => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_DAM,
                    self::UI_GROUP => UiGroups::GROUP_ASSET_EXTERNAL_PROVIDER,
                ],
            ],
            // UI
            UiPermissions::DAM_ASSET_LICENCE_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_EXT_SYSTEM_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_PERMISSION_GROUP_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_USER_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_AUTHOR_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_KEYWORD_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_DISTRIBUTION_CATEGORY_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_DISTRIBUTION_CATEGORY_SELECT_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::ADMIN_LOG_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_SIDEBAR_DIGITAL_MEDIA,
                ],
            ],
            UiPermissions::DAM_PODCAST_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
            UiPermissions::DAM_PODCAST_EPISODE_UI => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN_DAM,
                    self::UI_GROUP => UiGroups::GROUP_PODCAST,
                ],
            ],
        ];
    }

    public static function all(): array
    {
        return array_merge(
            BaseDamPermissions::ALL,
            UiPermissions::ALL,
            [
                self::DAM_USER_READ,
                self::DAM_USER_CREATE,
                self::DAM_USER_UPDATE,
                self::DAM_PERMISSION_GROUP_READ,
                self::DAM_PERMISSION_GROUP_CREATE,
                self::DAM_PERMISSION_GROUP_UPDATE,
                self::DAM_PERMISSION_GROUP_DELETE,
            ],
        );
    }

    public static function default(int $defaultGrant = Grants::GRANT_DENY): array
    {
        $resolved = [];
        foreach (self::all() as $permission) {
            $resolved[$permission] = $defaultGrant;
        }

        return $resolved;
    }

    public static function permissionAllowedValues(): array
    {
        $out = [];
        foreach (self::allDetail() as $permissionName => $detail) {
            $out[$permissionName] = $detail[self::GRANTS];
        }

        return $out;
    }
}
