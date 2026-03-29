<?php

declare(strict_types=1);

use ScienceStories\Mqtt\Client\PublishOptions;
use ScienceStories\Mqtt\Protocol\QoS;

test('PublishOptions can be created with default values', function (): void {
    $options = new PublishOptions();

    expect($options->qos)->toBe(QoS::AtMostOnce)
        ->and($options->retain)->toBeFalse()
        ->and($options->dup)->toBeFalse()
        ->and($options->properties)->toBeNull();
});

test('PublishOptions can be created with QoS 1', function (): void {
    $options = new PublishOptions(qos: QoS::AtLeastOnce);

    expect($options->qos)->toBe(QoS::AtLeastOnce);
});

test('PublishOptions can be created with QoS 2', function (): void {
    $options = new PublishOptions(qos: QoS::ExactlyOnce);

    expect($options->qos)->toBe(QoS::ExactlyOnce);
});

test('PublishOptions can be created with retain flag', function (): void {
    $options = new PublishOptions(retain: true);

    expect($options->retain)->toBeTrue();
});

test('PublishOptions can be created with dup flag', function (): void {
    $options = new PublishOptions(dup: true);

    expect($options->dup)->toBeTrue();
});

test('PublishOptions can be created with properties', function (): void {
    $properties = ['contentType' => 'application/json'];
    $options    = new PublishOptions(properties: $properties);

    expect($options->properties)->toBe($properties);
});

test('PublishOptions can be created with all parameters', function (): void {
    $properties = ['messageExpiryInterval' => 3600];
    $options    = new PublishOptions(
        qos: QoS::ExactlyOnce,
        retain: true,
        dup: false,
        properties: $properties
    );

    expect($options->qos)->toBe(QoS::ExactlyOnce)
        ->and($options->retain)->toBeTrue()
        ->and($options->dup)->toBeFalse()
        ->and($options->properties)->toBe($properties);
});
