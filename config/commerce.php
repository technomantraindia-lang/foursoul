<?php

return [
    'gateways' => [
        'cash' => App\Services\Commerce\ManualPaymentGateway::class,
        'bank_transfer' => App\Services\Commerce\ManualPaymentGateway::class,
        'log' => App\Services\Commerce\ManualPaymentGateway::class,
    ],
];
