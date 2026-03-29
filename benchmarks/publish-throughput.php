<?php

declare(strict_types=1);

/**
 * Benchmark: MQTT Publish Throughput
 *
 * Measures encoding performance and theoretical publish throughput
 * without actual network I/O.
 *
 * Usage: php benchmarks/publish-throughput.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ScienceStories\Mqtt\Protocol\Packet\Publish;
use ScienceStories\Mqtt\Protocol\QoS;
use ScienceStories\Mqtt\Protocol\V311\Encoder as V311Encoder;
use ScienceStories\Mqtt\Protocol\V5\Encoder as V5Encoder;

$iterations = 100_000;

echo "=== MQTT Publish Throughput Benchmark ===\n\n";

// --- Benchmark 1: V3.1.1 QoS 0 Encoding ---
$encoder = new V311Encoder();
$packet  = new Publish('bench/test/topic', 'Hello World payload for benchmarking', QoS::AtMostOnce, false, false);

$start = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $encoder->encodePublish($packet);
}
$elapsed = (hrtime(true) - $start) / 1e9;

printf(
    "V3.1.1 QoS 0 Encode:  %s iterations in %.3f sec → %s msg/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed)),
);

// --- Benchmark 2: V3.1.1 QoS 1 Encoding ---
$packet = new Publish('bench/test/topic', 'Hello World payload for benchmarking', QoS::AtLeastOnce, false, false, packetId: 1234);

$start = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $encoder->encodePublish($packet);
}
$elapsed = (hrtime(true) - $start) / 1e9;

printf(
    "V3.1.1 QoS 1 Encode:  %s iterations in %.3f sec → %s msg/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed)),
);

// --- Benchmark 3: V5.0 QoS 0 Encoding ---
$encoder5 = new V5Encoder();
$packet   = new Publish('bench/test/topic', 'Hello World payload for benchmarking', QoS::AtMostOnce, false, false);

$start = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $encoder5->encodePublish($packet);
}
$elapsed = (hrtime(true) - $start) / 1e9;

printf(
    "V5.0   QoS 0 Encode:  %s iterations in %.3f sec → %s msg/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed)),
);

// --- Benchmark 4: V5.0 with properties ---
$packet = new Publish(
    'bench/test/topic',
    'Hello World payload for benchmarking',
    QoS::AtLeastOnce,
    false,
    false,
    packetId: 1234,
    properties: [
        'payload_format_indicator' => 1,
        'message_expiry_interval'  => 3600,
        'content_type'             => 'application/json',
        'response_topic'           => 'bench/response',
        'correlation_data'         => 'req-12345',
    ],
);

$start = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $encoder5->encodePublish($packet);
}
$elapsed = (hrtime(true) - $start) / 1e9;

printf(
    "V5.0   QoS 1 + Props: %s iterations in %.3f sec → %s msg/sec\n",
    number_format($iterations),
    $elapsed,
    number_format((int) ($iterations / $elapsed)),
);

// --- Benchmark 5: Large Payload ---
$largePayload    = str_repeat('X', 65536); // 64 KB
$packet          = new Publish('bench/test/large', $largePayload, QoS::AtMostOnce, false, false);
$smallIterations = 10_000;

$start = hrtime(true);
for ($i = 0; $i < $smallIterations; $i++) {
    $encoder->encodePublish($packet);
}
$elapsed      = (hrtime(true) - $start)    / 1e9;
$throughputMB = ($smallIterations * 65536) / $elapsed / 1024 / 1024;

printf(
    "V3.1.1 64KB Payload:  %s iterations in %.3f sec → %.1f MB/sec\n",
    number_format($smallIterations),
    $elapsed,
    $throughputMB,
);

// --- Benchmark 6: Payload Sizes Comparison ---
echo "\n--- Payload Size Impact ---\n";
$sizes = [32, 128, 512, 1024, 4096, 16384, 65536];
foreach ($sizes as $size) {
    $payload = str_repeat('A', $size);
    $packet  = new Publish('bench/test', $payload, QoS::AtMostOnce, false, false);
    $iters   = $size > 4096 ? 10_000 : 50_000;

    $start = hrtime(true);
    for ($i = 0; $i < $iters; $i++) {
        $encoder->encodePublish($packet);
    }
    $elapsed = (hrtime(true) - $start) / 1e9;
    printf(
        "  %6s bytes: %s msg/sec\n",
        number_format($size),
        number_format((int) ($iters / $elapsed)),
    );
}

echo "\nBenchmark complete.\n";
