<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\CommonBundle\Exception\Handler\AccessDeniedExceptionHandler;
use AnzuSystems\CommonBundle\Exception\Handler\AppReadOnlyModeExceptionHandler;
use AnzuSystems\CommonBundle\Exception\Handler\DefaultExceptionHandler;
use AnzuSystems\CommonBundle\Exception\Handler\NotFoundExceptionHandler;
use AnzuSystems\CommonBundle\Exception\Handler\ValidationExceptionHandler;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\HealthCheck\Module\DataMountModule;
use AnzuSystems\CommonBundle\HealthCheck\Module\ForwardIpModule;
use AnzuSystems\CommonBundle\HealthCheck\Module\MongoModule;
use AnzuSystems\CommonBundle\HealthCheck\Module\MysqlModule;
use AnzuSystems\CommonBundle\HealthCheck\Module\OpCacheModule;
use AnzuSystems\CommonBundle\HealthCheck\Module\RedisModule;
use AnzuSystems\CoreDamBundle\Exception\RemoteProcessingWaitingException;
use App\Entity\User;
use App\Exception\PubNotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Bundle\FrameworkBundle\Command\CacheWarmupCommand;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Config\AnzuSystemsCommonConfig;

return static function (AnzuSystemsCommonConfig $config): void {
    $config->jobs()
        ->maxExecTime(env('ANZU_JOBS_MAX_EXEC_TIME')->int())
        ->maxMemory(env('byte_size:ANZU_JOBS_MAX_MEMORY')->int())
        ->noJobIdleTime(env('ANZU_JOBS_NO_JOB_IDLE_TIME')->int())
    ;

    $config
        ->settings()
            ->appRedis('DamRedis')
            ->appCacheProxyEnabled(true)
            ->userEntityClass(User::class)
            ->appEnumNamespace('App\Entity')
            ->appValueObjectNamespace('App\Model\ValueObject')
            ->unlockedCommands([
                AssetsInstallCommand::class,
                CacheWarmupCommand::class,
                ConsumeMessagesCommand::class,
            ])
    ;
    $config
        ->healthCheck()
            ->enabled(true)
            ->mysqlTableName('_doctrine_migration_versions')
            ->mongoCollections([
                'anzu_mongo_journal_log_collection',
                'anzu_mongo_audit_log_collection',
            ])
            ->modules([
                OpCacheModule::class,
                ForwardIpModule::class,
                MongoModule::class,
                MysqlModule::class,
                RedisModule::class,
                DataMountModule::class,
            ])
    ;
    $config
        ->errors()
            ->enabled(true)
            ->onlyUriMatch([
                '^/api/',
                '^/image/',
            ])
            ->defaultExceptionHandler(DefaultExceptionHandler::class)
            ->exceptionHandlers([
                NotFoundExceptionHandler::class,
                AppReadOnlyModeExceptionHandler::class,
                ValidationExceptionHandler::class,
                AccessDeniedExceptionHandler::class,
            ])
    ;
    $logsConfig = $config->logs();
    $logsConfig
        ->enabled(true)
            ->messengerTransport()
            ->name('core_dam_log')
            ->dsn(env('MESSENGER_TRANSPORT_DSN') . '/core_dam_log')
    ;
    $logsConfig
        ->app()
            ->ignoredExceptions([
                AccessDeniedException::class,
                UnauthorizedHttpException::class,
                NotFoundHttpException::class,
                ResourceNotFoundException::class,
                ValidationException::class,
                PubNotFoundHttpException::class,
                RemoteProcessingWaitingException::class,
            ])
    ;
    $logsConfig
        ->journal()
            ->mongo()
            ->uri(env('ANZU_MONGODB_APP_LOG_URI'))
            ->username(env('ANZU_MONGODB_APP_LOG_USERNAME'))
            ->password(env('ANZU_MONGODB_APP_LOG_PASSWORD'))
            ->database(env('ANZU_MONGODB_APP_LOG_DB'))
            ->ssl(env('ANZU_MONGODB_APP_LOG_SSL')->bool())
            ->collection('appLogs')
    ;
    $logsConfig
        ->audit()
            ->loggedMethods([Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH, Request::METHOD_DELETE])
            ->mongo()
                ->uri(env('ANZU_MONGODB_AUDIT_LOG_URI'))
                ->username(env('ANZU_MONGODB_AUDIT_LOG_USERNAME'))
                ->password(env('ANZU_MONGODB_AUDIT_LOG_PASSWORD'))
                ->database(env('ANZU_MONGODB_AUDIT_LOG_DB'))
                ->ssl(env('ANZU_MONGODB_AUDIT_LOG_SSL')->bool())
                ->collection('auditLogs')
    ;
};