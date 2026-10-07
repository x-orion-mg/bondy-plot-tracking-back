<?php 

namespace App\EventListener;

use Symfony\Component\Security\Core\User\UserInterface;

class LoginActivityLogger
{
    private string $logDirectory;

    public function __construct(string $logDirectory)
    {
        $this->logDirectory = $logDirectory;
    }

    public function logSuccess( ?UserInterface $user ): void
    {
        if ( $user ){
            if (!is_dir($this->logDirectory)) {
                mkdir($this->logDirectory, 0777, true);
            }
    
            $logEntry = sprintf(
                "[%s] SUCCESS: User '%s' logged in successfully from IP: %s\n",
                (new \DateTime())->format('Y-m-d H:i:s'),
                $user->getEmail(),
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            );
    
            $logFile = $this->logDirectory . '/login_activity_' . date('Y-m-d') . '.log';
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }

    public function logFailure( ?string $email, ?string $reason): void
    {
        if ( $email && $reason ){
            if (!is_dir($this->logDirectory)) {
                mkdir($this->logDirectory, 0777, true);
            }
    
            $logEntry = sprintf(
                "[%s] FAILURE: Failed login attempt for user '%s' from IP: %s. Reason: %s\n",
                (new \DateTime())->format('Y-m-d H:i:s'),
                $email,
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $reason
            );
    
            $logFile = $this->logDirectory . '/login_activity_' . date('Y-m-d') . '.log';
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
}
