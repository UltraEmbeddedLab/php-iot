<?php

declare(strict_types=1);

use ScienceStories\Mqtt\Client\TlsOptions;

test('default constructor values', function (): void {
    $tls = new TlsOptions();

    expect($tls->verifyPeer)->toBeTrue()
        ->and($tls->verifyPeerName)->toBeTrue()
        ->and($tls->allowSelfSigned)->toBeFalse()
        ->and($tls->caFile)->toBeNull()
        ->and($tls->caPath)->toBeNull()
        ->and($tls->clientCertificateFile)->toBeNull()
        ->and($tls->clientCertificateKeyFile)->toBeNull()
        ->and($tls->clientCertificateKeyPassphrase)->toBeNull()
        ->and($tls->peerName)->toBeNull()
        ->and($tls->alpn)->toBeNull()
        ->and($tls->sniEnabled)->toBeTrue();
});

test('withVerifyPeer returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withVerifyPeer(false);

    expect($original->verifyPeer)->toBeTrue()
        ->and($updated->verifyPeer)->toBeFalse()
        ->and($updated)->not->toBe($original);
});

test('withVerifyPeerName returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withVerifyPeerName(false);

    expect($original->verifyPeerName)->toBeTrue()
        ->and($updated->verifyPeerName)->toBeFalse()
        ->and($updated)->not->toBe($original);
});

test('withAllowSelfSigned returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withAllowSelfSigned(true);

    expect($original->allowSelfSigned)->toBeFalse()
        ->and($updated->allowSelfSigned)->toBeTrue()
        ->and($updated)->not->toBe($original);
});

test('withCaFile returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withCaFile('/path/to/ca.pem');

    expect($original->caFile)->toBeNull()
        ->and($updated->caFile)->toBe('/path/to/ca.pem')
        ->and($updated)->not->toBe($original);
});

test('withCaPath returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withCaPath('/path/to/certs');

    expect($original->caPath)->toBeNull()
        ->and($updated->caPath)->toBe('/path/to/certs')
        ->and($updated)->not->toBe($original);
});

test('withClientCertificate sets cert, key, and passphrase', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withClientCertificate('/cert.pem', '/key.pem', 'secret');

    expect($original->clientCertificateFile)->toBeNull()
        ->and($updated->clientCertificateFile)->toBe('/cert.pem')
        ->and($updated->clientCertificateKeyFile)->toBe('/key.pem')
        ->and($updated->clientCertificateKeyPassphrase)->toBe('secret')
        ->and($updated)->not->toBe($original);
});

test('withClientCertificate with null key file', function (): void {
    $tls = (new TlsOptions())->withClientCertificate('/combined.pem');

    expect($tls->clientCertificateFile)->toBe('/combined.pem')
        ->and($tls->clientCertificateKeyFile)->toBeNull()
        ->and($tls->clientCertificateKeyPassphrase)->toBeNull();
});

test('withPeerName returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withPeerName('broker.example.com');

    expect($original->peerName)->toBeNull()
        ->and($updated->peerName)->toBe('broker.example.com')
        ->and($updated)->not->toBe($original);
});

test('withAlpn returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withAlpn('mqtt');

    expect($original->alpn)->toBeNull()
        ->and($updated->alpn)->toBe('mqtt')
        ->and($updated)->not->toBe($original);
});

test('withSni returns new instance', function (): void {
    $original = new TlsOptions();
    $updated  = $original->withSni(false);

    expect($original->sniEnabled)->toBeTrue()
        ->and($updated->sniEnabled)->toBeFalse()
        ->and($updated)->not->toBe($original);
});

test('toStreamContext with defaults', function (): void {
    $ctx = (new TlsOptions())->toStreamContext();

    expect($ctx)->toHaveKey('ssl')
        ->and($ctx['ssl']['verify_peer'])->toBeTrue()
        ->and($ctx['ssl']['verify_peer_name'])->toBeTrue()
        ->and($ctx['ssl']['allow_self_signed'])->toBeFalse()
        ->and($ctx['ssl']['SNI_enabled'])->toBeTrue()
        ->and($ctx['ssl'])->not->toHaveKey('cafile')
        ->and($ctx['ssl'])->not->toHaveKey('local_cert')
        ->and($ctx['ssl'])->not->toHaveKey('alpn_protocols');
});

test('toStreamContext with full mTLS config', function (): void {
    $tls = (new TlsOptions())
        ->withCaFile('/ca.pem')
        ->withCaPath('/certs')
        ->withClientCertificate('/client.pem', '/client-key.pem', 'pass123')
        ->withPeerName('broker.local')
        ->withAlpn('mqtt')
        ->withAllowSelfSigned(true)
        ->withVerifyPeer(false);

    $ctx = $tls->toStreamContext();

    expect($ctx['ssl']['verify_peer'])->toBeFalse()
        ->and($ctx['ssl']['allow_self_signed'])->toBeTrue()
        ->and($ctx['ssl']['cafile'])->toBe('/ca.pem')
        ->and($ctx['ssl']['capath'])->toBe('/certs')
        ->and($ctx['ssl']['local_cert'])->toBe('/client.pem')
        ->and($ctx['ssl']['local_pk'])->toBe('/client-key.pem')
        ->and($ctx['ssl']['passphrase'])->toBe('pass123')
        ->and($ctx['ssl']['peer_name'])->toBe('broker.local')
        ->and($ctx['ssl']['alpn_protocols'])->toBe('mqtt');
});

test('fluent chaining preserves immutability', function (): void {
    $original = new TlsOptions();
    $chained  = $original
        ->withVerifyPeer(false)
        ->withCaFile('/ca.pem')
        ->withClientCertificate('/cert.pem');

    expect($original->verifyPeer)->toBeTrue()
        ->and($original->caFile)->toBeNull()
        ->and($original->clientCertificateFile)->toBeNull()
        ->and($chained->verifyPeer)->toBeFalse()
        ->and($chained->caFile)->toBe('/ca.pem')
        ->and($chained->clientCertificateFile)->toBe('/cert.pem');
});
