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
     * @var string
     */
    protected $errorMessage = '';

    /**
     * @var array
     */
    protected $tunnels = [];

    /**
     * Open SSH tunnels
     *
     * @return $this|array
     */
    public function openTunnels()
    {
        try {
            // Get YAML config
            $yamlConfig = Settings::getYamlConfig('ssh_tunnel');

            // Open SSH tunnels
            foreach ($yamlConfig as $sshConfig) {
                $sshTunnel = new SSH();
                $tunnelConfig = new Config();

                // Create config
                $tunnelConfig
                    ->setForwardHostRemote($sshConfig['forward_host_remote'])
                    ->setForwardPortLocal($sshConfig['forward_port_local'])
                    ->setForwardPortRemote($sshConfig['forward_port_remote'])
                    ->setPrivateKeyFilename($sshConfig['private_key_filename'])
                    ->setSshHostname($sshConfig['hostname'])
                    ->setSshPort($sshConfig['port'])
                    ->setSshUsername($sshConfig['username']);

                // Open SSH Tunnel
                $this->tunnels[] = $sshTunnel->openTunnel($tunnelConfig);
            }
        } catch (SSHException $e) {
            $this->setHasError(true)
                ->setErrorMessage($e->getMessage());

            $this->closeTunnels();
        }

        return $this;
    }

    /**
     * Close SSH tunnels
     *
     * @return bool|string
     */
    public function closeTunnels()
    {
        try {
            foreach ($this->tunnels as $sshTunnel) {
                /** @var SSH $sshTunnel */
                $sshTunnel->close();
            }
        } catch (SSHException $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @param $message
     * @return $this
     */
    protected function setErrorMessage($message)
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
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
    protected function setHasError($hasError)
    {
        $this->hasError = $hasError;

        return $this;
    }

    /**
     * @return array
     */
    public function getTunnels()
    {
        return $this->tunnels;
    }
}