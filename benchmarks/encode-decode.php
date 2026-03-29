<?php

declare(strict_types=1);

/**
 * Benchmark: MQTT Packet Encode/Decode round-trip
 *
 * Measures the full encode→decode cycle for various packet types.
 *
 * Usage: php benchmarks/encode-decode.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ScienceStories\Mqtt\Protocol\Packet\Connect;
use ScienceStories\Mqtt\Protocol\Packet\Publish;
use ScienceStories\Mqtt\Protocol\QoS;
use ScienceStories\Mqtt\Protocol\V311\Decoder as V311Decoder;
use ScienceStories\Mqtt\Protocol\V311\Encoder as V311Encoder;
use ScienceStories\Mqtt\Protocol\V5\Decoder as V5Decoder;
use ScienceStories\Mqtt\Protocol\V5\Encoder as V5Encoder;

$iterations = 50_000;

echo "=== MQTT Encode/Decode Round-Trip Benchmark ===\n\n";

// --- V3.1.1 ---
$enc311 = new V311Encoder();
$dec311 = new V311Decoder();

// CONNECT
$connect = new Connect('bench-client-id', 60, true, 'user', 'pass');
$start   = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $enc311->encodeConnect($connect);
}
$elapsed = (hrtime(true) - $start) / 1e9;
printf(
    "V3.1.1 CONNECT Encode:    %s iter in %.3f sec → %s ops/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed))
);

// PUBLISH encode + decode
$publish = new Publish('test/topic', '{"temp":22.5}', QoS::AtLeastOnce, false, false, packetId: 100);
$encoded = $enc311->encodePublish($publish);
// Extract flags and body from encoded packet
$flags311   = (ord($encoded[0]) & 0x0F);
$bodyOffset = 1;
$byte       = ord($encoded[$bodyOffset]);
while ($byte & 0x80) {
    $bodyOffset++;
    $byte = ord($encoded[$bodyOffset]);
}
$bodyOffset++;
$body311 = substr($encoded, $bodyOffset);

$start = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $enc311->encodePublish($publish);
    $dec311->decodePublish($flags311, $body311);
}
$elapsed = (hrtime(true) - $start) / 1e9;
printf(
    "V3.1.1 PUBLISH Roundtrip: %s iter in %.3f sec → %s ops/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed))
);

// --- V5.0 ---
$enc5 = new V5Encoder();
$dec5 = new V5Decoder();

// PUBLISH with properties
$publish5 = new Publish(
    'test/topic',
    '{"temp":22.5}',
    QoS::AtLeastOnce,
    false,
    false,
    packetId: 100,
    properties: [
        'payload_format_indicator' => 1,
        'content_type'             => 'application/json',
    ],
);
$encoded5    = $enc5->encodePublish($publish5);
$flags5      = (ord($encoded5[0]) & 0x0F);
$bodyOffset5 = 1;
$byte5       = ord($encoded5[$bodyOffset5]);
while ($byte5 & 0x80) {
    $bodyOffset5++;
    $byte5 = ord($encoded5[$bodyOffset5]);
}
$bodyOffset5++;
$body5 = substr($encoded5, $bodyOffset5);

$start = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $enc5->encodePublish($publish5);
    $dec5->decodePublish($flags5, $body5);
}
$elapsed = (hrtime(true) - $start) / 1e9;
printf(
    "V5.0   PUBLISH Roundtrip: %s iter in %.3f sec → %s ops/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed))
);

// --- Memory usage ---
echo "\n--- Memory Usage ---\n";
printf("Peak memory: %s KB\n", number_format(memory_get_peak_usage(true) / 1024));
printf("Current memory: %s KB\n", number_format(memory_get_usage(true) / 1024));

echo "\nBenchmark complete.\n";
