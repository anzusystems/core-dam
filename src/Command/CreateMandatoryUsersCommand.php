<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CommonBundle\Domain\User\CurrentAnzuUserProvider;
use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\Contracts\Entity\AnzuUser;
use App\Domain\User\UserManager;
use App\Entity\User;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

#[AsCommand(
    name: 'anzu:mandatory-create:user',
    description: 'Create mandatory users.'
)]
final class CreateMandatoryUsersCommand extends Command
{
    private QuestionHelper $questionHelper;

    private const ADMIN_USER_SSO_ID_ARG = 'sso-id';

    public function __construct(
        private readonly UserManager $userManager,
        private readonly CurrentAnzuUserProvider $currentAnzuUserProvider,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addOption(
                name: self::ADMIN_USER_SSO_ID_ARG,
                mode: InputArgument::OPTIONAL,
                default: '673348',
            );
    }


    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $anonymousUserId = AnzuApp::getUserIdAnonymous();
        $consoleUserId = AnzuApp::getUserIdConsole();
        $adminUserId = AnzuApp::getUserIdAdmin();

        $this->questionHelper = $this->getHelper('question');

        $userClassMetadata = $this->userManager->getEntityManager()->getClassMetadata(User::class);
        $userClassMetadata->setIdGenerator(new AssignedGenerator());
        $userClassMetadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

        $anonymousUser = $this->userManager->getEntityManager()->find(User::class, $anonymousUserId);
        if ($anonymousUser instanceof AnzuUser) {
            $output->writeln('Anonymous user already exists. Skipping...');
        }

        if (null === $anonymousUser) {
            $output->writeln('<info>Anonymous user (' . $anonymousUserId . ') not found. Creating...</info>');
            $email = $this->askForEmail($input, $output, 'dam_anonymous@anzusystems.dev');

            $anonymousUser = new User();
            $anonymousUser->setId($anonymousUserId);
            $anonymousUser->setEmail($email);
            $anonymousUser->setFirstName('Anonymous');
            $anonymousUser->setLastName('DAM');
            $anonymousUser->setEnabled(false);

            $this->userManager->getEntityManager()->persist($anonymousUser);
            $this->currentAnzuUserProvider->setCurrentUser($anonymousUser);

            $this->userManager->create($anonymousUser);
        }

        $consoleUser = $this->userManager->getEntityManager()->find(User::class, $consoleUserId);
        if ($consoleUser instanceof AnzuUser) {
            $output->writeln('Console user already exists. Skipping...');
        }

        if (null === $consoleUser) {
            $output->writeln('<info>Console user (' . $consoleUserId . ') not found. Creating...</info>');
            $email = $this->askForEmail($input, $output, 'dam_console@anzusystems.dev');

            $consoleUser = new User();
            $consoleUser->setId($consoleUserId);
            $consoleUser->setEmail($email);
            $consoleUser->setFirstName('Console');
            $consoleUser->setLastName('DAM');
            $consoleUser->setEnabled(false);

            $this->userManager->create($consoleUser);
        }

        $adminUser = $this->userManager->getEntityManager()->find(User::class, $adminUserId);
        if ($adminUser instanceof AnzuUser) {
            $output->writeln('Admin user already exists. Skipping...');
        }

        if (null === $adminUser) {
            $output->writeln('<info>Admin user (' . $adminUserId . ') not found. Creating...</info>');
            $email = $this->askForEmail($input, $output, 'dam_admin@anzusystems.dev');

            $ssoId = $input->getOption(self::ADMIN_USER_SSO_ID_ARG);
            $output->writeln('SSO ID for admin user is: ' . $ssoId);

            $adminUser = new User();
            $adminUser->setId($adminUserId);
            $adminUser->setEmail($email);
            $adminUser->setFirstName('Admin');
            $adminUser->setLastName('DAM');
            $adminUser->setEnabled(true);
            $adminUser->setRoles([User::ROLE_ADMIN]);
            $adminUser->setSsoId($ssoId);

            $this->userManager->create($adminUser);
        }

        return self::SUCCESS;
    }

    private function askForEmail(InputInterface $input, OutputInterface $output, ?string $default = null): string
    {
        $question = 'Please enter the email of admin user';
        if ($default) {
            $question .= ' (default "' . $default . '")';
        }
        $question .= ': ';

        $emailQuestion = new Question($question, $default);
        $emailValidation = Validation::createCallable(new Email());
        $emailQuestion->setValidator($emailValidation);

        return $this->questionHelper->ask($input, $output, $emailQuestion);
    }
}
