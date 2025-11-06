<?php

declare(strict_types=1);

namespace SergeevPasha\Pecom\DTO;

use Carbon\Carbon;
use Spatie\DataTransferObject\DataTransferObject;

class PecomTrack extends DataTransferObject
{
    /**
     * @var string|null
     */
    public ?string $status;

    /**
     * @var string
     */
    public string $link;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $startDate;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $receiveDate;

    /**
     * From Array.
     *
     * @param array $data
     *
     * @return self
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function fromArray(array $data): self
    {
        $cargoInfo = $data['cargos'][0]['info'] ?? [];

        $status = $cargoInfo['cargoStatus'] ?? null;
        $orderId = $data['cargos'][0]['cargo']['code'] ?? '';
        $link = $orderId ? 'https://pecom.ru/services-are/order-status/?code=' . $orderId : '';


        $startDate = isset($cargoInfo['takeOnStockDateTime'])
            ? Carbon::parse($cargoInfo['takeOnStockDateTime'])
            : null;

        $receiveDate = isset($cargoInfo['receivedByClientDateTime'])
            ? Carbon::parse($cargoInfo['receivedByClientDateTime'])
            : (isset($cargoInfo['giveOutDateTime'])
                ? Carbon::parse($cargoInfo['giveOutDateTime'])
                : null);

        return new self([
            'status'            => $status,
            'link'              => $link,
            'startDate'         => $startDate,
            'receiveDate'       => $receiveDate,
        ]);
    }
}
