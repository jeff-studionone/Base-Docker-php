<?php
require './vendor/autoload.php';

use Amp\Delayed;
use Amp\Websocket;


// Connects to the websocket endpoint in demo.php provided with Aerys (https://github.com/amphp/aerys).
Amp\Loop::run(function () {
    /** @var \Amp\Websocket\Connection $connection */
    // if (empty(getenv('WS_SERVER')) && empty(getenv('WS_TOKEN'))) {
    //     return null;
    // }

    $apiToken = 'HHUSE0jaf6oMGtFgbuNYHHNeiK9NgdVM';
    $server = 'ws.dev.accelerator2.studionone.io';
    // $apiToken = getenv('WS_TOKEN');
    // $server = getenv('WS_SERVER');

    $handshake = new Websocket\Handshake('wss://' . $server . '/admin/tourism-media/' . $apiToken);
    // $handshake->addHeader('Authorization', $apiToken);
    $connection = yield Websocket\connect($handshake);

    $path = '/var/www/html/gearbox3/console.php AcceleratorFunctions';
    $actions = [
        'campaign.submission.new' => 'php '. $path .' mode=updateSubmission campaignId=%s submissionId=%s',
        'campaign.submission.edit' => 'php '. $path .' mode=updateSubmission campaignId=%s submissionId=%s',
        'campaign.finalise' => 'php '. $path .' mode=incrementalDataImport campaignId=%s',
    ];

    while ($message = yield $connection->receive()) {
        $payload = yield $message->buffer();
        $decodedPayload = json_decode($payload);

        printf("Received: %s\n", $payload);

        if (!is_array($decodedPayload) && is_object($decodedPayload) && $actions[$decodedPayload->_type]) {
            $submissionId = $decodedPayload->id;

            if (property_exists($decodedPayload, 'campaign')) {
                $campaignId = $decodedPayload->campaign;
                $consoleCommand = sprintf($actions[$decodedPayload->_type], $submissionId, $campaignId);
            } else {
                $consoleCommand = sprintf($actions[$decodedPayload->_type], $submissionId);
            }
            printf("command: %s\n", $consoleCommand);

            // echo exec ($consoleCommand);
        }
    }
});
