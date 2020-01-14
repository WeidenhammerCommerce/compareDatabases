<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2020 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Fastbolt\SSH\SSH;
use Fastbolt\SSH\Config;
use Fastbolt\SSH\SSHException;

class Tunnel
{
    /**
     * @var bool
     */
    protected $hasError = false;

    /**
     * @var SSH
     */
    protected $firstTunnel;

    /**
     * @var SSH
     */
    protected $secondTunnel;

    /**
     * Open SSH tunnels
     *
     * @return array
     */
    public function openTunnels()
    {
        try {
            // Open first SSH Tunnel
            $sshTunnel = new SSH();
            $sshConfig = new Config();

            $sshConfig
                ->setForwardHostRemote(Settings::SSH1_FORWARD_HOST_REMOTE)
                ->setForwardPortLocal(Settings::SSH1_FORWARD_PORT_LOCAL)
                ->setForwardPortRemote(Settings::SSH1_FORWARD_PORT_REMOTE)
                ->setPrivateKeyFilename(Settings::SSH1_PRIVATE_KEY_FILENAME)
                ->setSshHostname(Settings::SSH1_HOSTNAME)
                ->setSshPort(Settings::SSH1_PORT)
                ->setSshUsername(Settings::SSH1_USERNAME);
            $this->firstTunnel = $sshTunnel->openTunnel($sshConfig);

            // Open second SSH Tunnel
            $sshTunnel = new SSH();
            $sshConfig = new Config();

            $sshConfig
                ->setForwardHostRemote(Settings::SSH2_FORWARD_HOST_REMOTE)
                ->setForwardPortLocal(Settings::SSH2_FORWARD_PORT_LOCAL)
                ->setForwardPortRemote(Settings::SSH2_FORWARD_PORT_REMOTE)
                ->setPrivateKeyFilename(Settings::SSH2_PRIVATE_KEY_FILENAME)
                ->setSshHostname(Settings::SSH2_HOSTNAME)
                ->setSshPort(Settings::SSH2_PORT)
                ->setSshUsername(Settings::SSH2_USERNAME);
            $this->secondTunnel = $sshTunnel->openTunnel($sshConfig);
        } catch (SSHException $e) {
            $this->setHasError(true);
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Close SSH tunnel
     *
     * @return bool|string
     */
    public function closeTunnels()
    {
        try {
            $this->firstTunnel->close();
            $this->secondTunnel->close();
        } catch (SSHException $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getHasError()
    {
        return $this->hasError;
    }

    /**
     * @param $hasError
     * @return $this
     */
    public function setHasError($hasError)
    {
        $this->hasError = $hasError;

        return $this;
    }

    /**
     * @return SSH
     */
    protected function getFirstTunnel()
    {
        return $this->firstTunnel;
    }

    /**
     * @return SSH
     */
    protected function getSecondTunnel()
    {
        return $this->secondTunnel;
    }
}