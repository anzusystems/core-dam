<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\App;
use App\Entity\User;
use App\Model\MediaApiMigrateConfig;
use DateTimeInterface;
use Doctrine\DBAL\Connection;

final class UsersMigration
{
    use OutputUtilTrait;

    private readonly ConnectionDecorator $connectionDecorator;

    /**
     * @var array<int, null|string>
     */
    private array $cache = [];

    public function __construct(
        private readonly MigrationTableIterator $migrationIterator,
        private readonly OAuth2HttpClient $OAuth2HttpClient,
        private readonly Connection $defaultConnection,
    ) {
        $this->connectionDecorator = new ConnectionDecorator($this->defaultConnection);
    }

    public function migrate(MediaApiMigrateConfig $config): void
    {
        if (false === $config->allowMigrateUsersStage()) {
            return;
        }

        $progress = $this->outputUtil->createProgressBar($this->migrationIterator->getCount($config));
        $progress->setFormat('debug');
        $progress->start();

        foreach ($this->migrationIterator->iterate($config) as $row) {
            $this->createUser($row->getCreatedById());
            $this->createUser($row->getUpdatedById());
            $progress->advance();
        }
    }

    protected function getEmail(int $userId): ?string
    {
        try {
            return $this->OAuth2HttpClient->getSsoUserInfo((string) $userId)->getEmail();
        } catch (UnsuccessfulAccessTokenRequestException | UnsuccessfulUserInfoRequestException) {
            $this->outputUtil->info(sprintf('User id (%s) missing, using fake address', $userId));

            return 'dam-' . $userId . '@anzusystems.dev';
        }
    }

    private function createUser(int $userId): ?string
    {
        if (isset($this->cache[$userId])) {
            return $this->cache[$userId];
        }

        $email = $this->getEmail($userId);
        $this->connectionDecorator->prepareBulkInsert(
            'user',
            [
                'id' => $userId,
                'created_at' => App::getAppDate()->format(DateTimeInterface::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeInterface::ATOM),
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'roles' => json_encode(['ROLE_DAM_ADMIN']),
                'enabled' => '0',
                'email' => $email,
                'person_first_name' => '',
                'person_last_name' => '',
                'person_full_name' => '',
                'avatar_color' => '',
                'avatar_text' => '',
                'api_token' => null,
                'permissions' => '{}',
                'allowed_asset_external_providers' => '[]',
                'allowed_distribution_services' => '[]',
                'selected_licence_id' => null,
            ]
        );
        $this->connectionDecorator->flush();
        $this->cache[$userId] = $email;

        return $email;
    }
}
