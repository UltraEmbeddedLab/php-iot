<?php

declare(strict_types=1);

use ScienceStories\Mqtt\Client\WillOptions;
use ScienceStories\Mqtt\Protocol\QoS;

test('default constructor values', function (): void {
    $will = new WillOptions(topic: '', payload: '');

    expect($will->topic)->toBe('');
    expect($will->payload)->toBe('');
    expect($will->qos)->toBe(QoS::AtMostOnce);
    expect($will->retain)->toBe(false);
    expect($will->properties)->toBe(null);
});

test('withTopic returns new instance', function (): void {
    $will = new WillOptions(topic: 'old', payload: 'data');
    $new  = $will->withTopic('new');

    expect($new->topic)->toBe('new');
    expect($new)->not->toBe($will);
});

test('withPayload returns new instance', function (): void {
    $will = new WillOptions(topic: 'test', payload: 'old');
    $new  = $will->withPayload('new');

    expect($new->payload)->toBe('new');
    expect($new)->not->toBe($will);
});

test('withQos returns new instance', function (): void {
    $will = new WillOptions(topic: 'test', payload: 'data');
    $new  = $will->withQos(QoS::ExactlyOnce);

    expect($new->qos)->toBe(QoS::ExactlyOnce);
    expect($new)->not->toBe($will);
});

test('withRetain returns new instance', function (): void {
    $will = new WillOptions(topic: 'test', payload: 'data');
    $new  = $will->withRetain(true);

    expect($new->retain)->toBe(true);
    expect($new)->not->toBe($will);
});

test('immutability - original not modified after with*()', function (): void {
    $original = new WillOptions(topic: 'original', payload: 'data', qos: QoS::AtMostOnce, retain: false);

    $original->withTopic('changed');
    $original->withPayload('changed');
    $original->withQos(QoS::ExactlyOnce);
    $original->withRetain(true);

    expect($original->topic)->toBe('original');
    expect($original->payload)->toBe('data');
    expect($original->qos)->toBe(QoS::AtMostOnce);
    expect($original->retain)->toBe(false);
});
