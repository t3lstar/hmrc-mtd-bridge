<?php

use App\Services\TaxYearService;

test('it assigns uk tax year quarters using hmrc boundaries', function () {
    $service = app(TaxYearService::class);

    expect($service->taxYearStart('2025-04-05'))->toBe(2024);
    expect($service->taxYearStart('2025-04-06'))->toBe(2025);

    expect($service->quarterFor('2025-04-06'))->toBe(1);
    expect($service->quarterFor('2025-07-05'))->toBe(1);
    expect($service->quarterFor('2025-07-06'))->toBe(2);
    expect($service->quarterFor('2025-10-06'))->toBe(3);
    expect($service->quarterFor('2026-01-05'))->toBe(3);
    expect($service->quarterFor('2026-01-06'))->toBe(4);
    expect($service->quarterFor('2026-04-05'))->toBe(4);
});
