<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\AuthBundle\Model\SsoUserDto;
use App\Domain\User\UgcUserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:user:create-ugc',
    description: 'Create a UGC user from SSO user ID and email',
)]
final class CreateUgcUserCommand extends Command
{
    private const string ARG_ID = 'id';
    private const string ARG_EMAIL = 'email';

    public function __construct(
        private readonly UgcUserManager $userManager,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addArgument(
                name: self::ARG_ID,
                mode: InputArgument::REQUIRED,
                description: 'SSO user ID',
            )
            ->addArgument(
                name: self::ARG_EMAIL,
                mode: InputArgument::REQUIRED,
                description: 'User email address',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ssoUserDto = (new SsoUserDto())
            ->setId($input->getArgument(self::ARG_ID))
            ->setEmail($input->getArgument(self::ARG_EMAIL));

        $user = $this->userManager->createFromSsoUserInfo($ssoUserDto, flush: true);

        $output->writeln(sprintf('<info>UGC user created with ID %s</info>', $user->getId()));

        return Command::SUCCESS;
    }
}
