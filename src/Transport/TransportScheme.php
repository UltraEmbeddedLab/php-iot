<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Transport;

enum TransportScheme: string
{
    case TCP = 'tcp';
    case TLS = 'tls';
    case WS  = 'ws';
    case WSS = 'wss';
}
