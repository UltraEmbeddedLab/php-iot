<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Client;

/**
 * Immutable TLS/SSL configuration for MQTT connections.
 *
 * Covers mutual TLS (client certificates), CA verification, ALPN,
 * and self-signed certificate allowance.
 */
final class TlsOptions
{
    public function __construct(
        public bool $verifyPeer = true,
        public bool $verifyPeerName = true,
        public bool $allowSelfSigned = false,
        public ?string $caFile = null,
        public ?string $caPath = null,
        public ?string $clientCertificateFile = null,
        public ?string $clientCertificateKeyFile = null,
        public ?string $clientCertificateKeyPassphrase = null,
        public ?string $peerName = null,
        public ?string $alpn = null,
        public bool $sniEnabled = true,
    ) {
    }

    public function withVerifyPeer(bool $verify): self
    {
        $c             = clone $this;
        $c->verifyPeer = $verify;

        return $c;
    }

    public function withVerifyPeerName(bool $verify): self
    {
        $c                 = clone $this;
        $c->verifyPeerName = $verify;

        return $c;
    }

    public function withAllowSelfSigned(bool $allow): self
    {
        $c                  = clone $this;
        $c->allowSelfSigned = $allow;

        return $c;
    }

    public function withCaFile(?string $path): self
    {
        $c         = clone $this;
        $c->caFile = $path;

        return $c;
    }

    public function withCaPath(?string $path): self
    {
        $c         = clone $this;
        $c->caPath = $path;

        return $c;
    }

    public function withClientCertificate(?string $certFile, ?string $keyFile = null, ?string $passphrase = null): self
    {
        $c                                 = clone $this;
        $c->clientCertificateFile          = $certFile;
        $c->clientCertificateKeyFile       = $keyFile;
        $c->clientCertificateKeyPassphrase = $passphrase;

        return $c;
    }

    public function withPeerName(?string $name): self
    {
        $c           = clone $this;
        $c->peerName = $name;

        return $c;
    }

    public function withAlpn(?string $protocol): self
    {
        $c       = clone $this;
        $c->alpn = $protocol;

        return $c;
    }

    public function withSni(bool $enabled): self
    {
        $c             = clone $this;
        $c->sniEnabled = $enabled;

        return $c;
    }

    /**
     * Convert to stream context options array for PHP's ssl wrapper.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toStreamContext(): array
    {
        $ssl = [
            'verify_peer'       => $this->verifyPeer,
            'verify_peer_name'  => $this->verifyPeerName,
            'allow_self_signed' => $this->allowSelfSigned,
            'SNI_enabled'       => $this->sniEnabled,
        ];

        if ($this->caFile !== null) {
            $ssl['cafile'] = $this->caFile;
        }

        if ($this->caPath !== null) {
            $ssl['capath'] = $this->caPath;
        }

        if ($this->clientCertificateFile !== null) {
            $ssl['local_cert'] = $this->clientCertificateFile;
        }

        if ($this->clientCertificateKeyFile !== null) {
            $ssl['local_pk'] = $this->clientCertificateKeyFile;
        }

        if ($this->clientCertificateKeyPassphrase !== null) {
            $ssl['passphrase'] = $this->clientCertificateKeyPassphrase;
        }

        if ($this->peerName !== null) {
            $ssl['peer_name'] = $this->peerName;
        }

        if ($this->alpn !== null) {
            $ssl['alpn_protocols'] = $this->alpn;
        }

        return ['ssl' => $ssl];
    }
}
