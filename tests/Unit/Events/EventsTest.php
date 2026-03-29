<?php

declare(strict_types=1);

use ScienceStories\Mqtt\Client\InboundMessage;
use ScienceStories\Mqtt\Events\MessageReceived;
use ScienceStories\Mqtt\Events\PacketReceived;
use ScienceStories\Mqtt\Events\PacketSent;
use ScienceStories\Mqtt\Events\ServerDisconnect;
use ScienceStories\Mqtt\Protocol\Packet\Disconnect;
use ScienceStories\Mqtt\Protocol\QoS;

test('MessageReceived stores message', function (): void {
    $message = new InboundMessage(
        topic: 'sensors/temp',
        payload: '22.5',
        qos: QoS::AtLeastOnce,
        retain: false,
        dup: false,
        packetId: 1,
    );

    $event = new MessageReceived($message);

    expect($event->message)->toBe($message);
    expect($event->message->topic)->toBe('sensors/temp');
    expect($event->message->payload)->toBe('22.5');
    expect($event->message->qos)->toBe(QoS::AtLeastOnce);
});

test('PacketReceived stores all properties', function (): void {
    $event = new PacketReceived(
        bytes: "\x30\x05",
        packetType: 3,
        flags: 0,
        remainingLength: 5,
    );

    expect($event->bytes)->toBe("\x30\x05");
    expect($event->packetType)->toBe(3);
    expect($event->flags)->toBe(0);
    expect($event->remainingLength)->toBe(5);
});

test('PacketSent stores bytes and type', function (): void {
    $event = new PacketSent(
        bytes: "\xC0\x00",
        packetType: 12,
    );

    expect($event->bytes)->toBe("\xC0\x00");
    expect($event->packetType)->toBe(12);
});

test('ServerDisconnect stores disconnect and willReconnect', function (): void {
    $disconnect = new Disconnect(reasonCode: 0x8B);
    $event      = new ServerDisconnect($disconnect, willReconnect: true);

    expect($event->disconnect)->toBe($disconnect);
    expect($event->willReconnect)->toBe(true);
});

test('ServerDisconnect isNormal for code 0', function (): void {
    $disconnect = new Disconnect(reasonCode: 0x00);
    $event      = new ServerDisconnect($disconnect);

    expect($event->isNormal())->toBe(true);
    expect($event->isError())->toBe(false);
});

test('ServerDisconnect isError for code >= 0x80', function (): void {
    $disconnect = new Disconnect(reasonCode: 0x8B);
    $event      = new ServerDisconnect($disconnect);

    expect($event->isError())->toBe(true);
    expect($event->isNormal())->toBe(false);
});

test('ServerDisconnect getReasonDescription returns meaningful text', function (): void {
    $disconnect = new Disconnect(reasonCode: 0x8B);
    $event      = new ServerDisconnect($disconnect);

    $description = $event->getReasonDescription();

    expect($description)->toBe('Server shutting down');
});
