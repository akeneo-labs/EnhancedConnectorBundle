<?php

namespace Pim\Bundle\EnhancedConnectorBundle\FileSender;

use Ssh\Configuration;
use Ssh\Authentication;
use Ssh\Session;
use Ssh\Sftp;

/**
 * Utility class to send file by SFTP
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SftpFileSender
{
    /** @staticvar */
    const SSH_PORT = 22;

    /** @var array */
    protected $sftpClients = [];

    /**
     * Send local file to remote file
     *
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     * @param string $localPath
     * @param string $remotePath
     */
    public function sendFile($host, $port = self::SSH_PORT, $user, $pass, $localPath, $remotePath)
    {
        $remoteDir = dirname($remotePath);
        $remoteFilename = basename($remotePath);

        $sftpClient = $this->getSftpClient($host, $port, $user, $pass, $remoteDir);
        $writeResult = $sftpClient->write($remotePath, file_get_contents($localPath));

        if (false === $writeResult) {
            throw new \Exception(
                sprintf(
                    'The file "%s" cannot be sent to "%s@%s:%s". '.
                    'Please check your SFTP parameters',
                    $localpath,
                    $user,
                    $host,
                    $remotePath
                )
            );
        }

    }

    /**
     * Get a SFTP client or generate one if it does not exist yet
     *
     * @param $host
     * @param $user
     * @param $pass
     * @param $dir
     *
     * @return Sftp
     */
    protected function getSftpClient($host, $port = self::SSH_PORT, $user, $pass, $dir)
    {
        if (!isset($this->sftpClients[$host][$user][$dir])) {
            $sshConfig  = new Configuration($host, $port);
            $sshAuth    = new Authentication\Password($user, $pass);
            $sshSession = new Session($sshConfig, $sshAuth);

            $sftpClient = $sshSession->getSftp();
            if (!$sftpclient) {
                throw new \Exception(
                    sprintf(
                        'Unable to connect the SFTP client on "%s@%s:%s" with port "%s"'.
                        'Please check your SFTP parameters',
                        $user,
                        $host,
                        $dir,
                        $port
                    )
                );
            }
            $this->sftpClients[$host][$user][$dir] = $sftpClient;

        }

        return $this->sftpClients[$host][$user][$dir];
    }
}
