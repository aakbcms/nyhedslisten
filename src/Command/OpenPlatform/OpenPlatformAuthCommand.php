<?php

/**
 * @file
 */

namespace App\Command\OpenPlatform;

use App\Exception\PlatformAuthException;
use App\Service\OpenPlatform\AuthenticationService;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OpenPlatformAuthCommand.
 */
#[ AsCommand('app:openplatform:auth', 'Use environment configuration to test authentication')]
class OpenPlatformAuthCommand extends Command
{
    private bool $refresh = false;

    /**
     * OpenPlatformAuthCommand constructor.
     *
     * @param authenticationService $authentication
     *   Open Platform authentication service
     */
    public function __construct(
        private readonly AuthenticationService $authentication
    ) {
        parent::__construct();
    }

    /**
     * Define the command.
     */
    protected function configure()
    {
        $this->setHelp('Gets oAuth2 access token to the Open Platform')
            ->addArgument('refresh', InputArgument::OPTIONAL, 'Refresh the access token');
    }

    /**
     * {@inheritdoc}
     *
     * Uses the authentication service to get an access token form the open
     * platform.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arg = $input->getArgument('refresh');
        $this->refresh = $arg ? (bool) $arg : $this->refresh;

        try {
            $token = $this->authentication->getAccessToken($this->refresh);

            $msg = 'Access token: '.$token;
            $separator = str_repeat('-', \strlen($msg) + 2);
            $output->writeln($separator);
            $output->writeln(' Access token: '.$token);
            $output->writeln($separator);

            return Command::SUCCESS;
        } catch (PlatformAuthException|GuzzleException|InvalidArgumentException $e) {
            $output->writeln($e);

            return Command::FAILURE;
        }
    }
}
