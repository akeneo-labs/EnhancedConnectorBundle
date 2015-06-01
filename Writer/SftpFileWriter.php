<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Pim\Bundle\EnhancedConnectorBundle\FileSender\SftpFileSender;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Override to allow to send CSV file to SFTP
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SftpFileWriter extends FileWriter
{
    /**
     * @var resource
     */
    protected $handler;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $host;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $port = '22';

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $user;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $pass;

    /**
     * @var SftpFileSender
     */
    protected $fileSender;

    /** @var string */
    protected $originalPath;

    /**
     * @param SftpFileSender $fileSender
     */
    public function __construct(SftpFileSender $fileSender)
    {
        $this->fileSender = $fileSender;
    }

    /**
     * Set the SFTP hostname
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get the SFTP hostname
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the SFTP port number
     *
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get the SFTP port number
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the SFTP username
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get the SFTP username
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the SFTP pass
     *
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * Get the SFTP pass
     *
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        $this->originalPath = $this->getPath();

        $temporaryPath = tempnam(sys_get_temp_dir(), basename($this->originalPath));

        $this->resolvedFilePath = $temporaryPath;

        parent::write($data);
    }

    /**
     * Override of the flush to send CSV files to SFTP
     */
    public function flush()
    {
        if ($this->handler) {
            fclose($this->handler);
            $this->handler = null;
        }

        $this->fileSender->sendFile(
            $this->host,
            $this->port,
            $this->user,
            $this->pass,
            $this->getPath(),
            $this->originalPath
        );
        $stepExecution->incrementSummaryInfo('sftp_file_sent');
    }


    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return
            array_merge(
                parent::getConfigurationFields(),
                array(
                    'host' => array(
                        'options' => array(
                            'label' => 'pim_enhanced_connector.export.sftp.host.label',
                            'help'  => 'pim_enhanced_connector.export.sftp.host.help'
                        )
                    ),
                    'port' => array(
                        'options' => array(
                            'label' => 'pim_enhanced_connector.export.sftp.user.label',
                            'help'  => 'pim_enhanced_connector.export.sftp.user.help'
                        )
                    ),
                    'user' => array(
                        'options' => array(
                            'label' => 'pim_enhanced_connector.export.sftp.user.label',
                            'help'  => 'pim_enhanced_connector.export.sftp.user.help'
                        )
                    ),
                    'pass' => array(
                        'type' => 'password',
                        'options' => array(
                            'label' => 'pim_enhanced_connector.export.sftp.pass.label',
                            'help'  => 'pim_enhanced_connector.export.sftp.pass.help'
                        )
                    ),
                )
            );
    }
}
