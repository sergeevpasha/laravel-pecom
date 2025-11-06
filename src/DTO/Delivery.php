<?php

declare(strict_types=1);

namespace SergeevPasha\Pecom\DTO;

use Spatie\DataTransferObject\DataTransferObject;
use SergeevPasha\Pecom\DTO\Collection\CargoCollection;

class Delivery extends DataTransferObject
{
    /**
     * @var int
     */
    public int $arrivalCityId;
    
    /**
     * @var int
     */
    public int $derivalCityId;

    /**
     * @var bool
     */
    public bool $arrivalOpenCar;

    /**
     * @var bool
     */
    public bool $derivalOpenCar;

    /**
     * @var int
     */
    public int $arrivalDistanceType;

    /**
     * @var int
     */
    public int $derivalDistanceType;

    /**
     * @var bool
     */
    public bool $oneDayDelivery;

    /**
     * @var bool
     */
    public bool $isShop;

    /**
     * @var string
     */
    public string $payDate;
    
    /**
     * @var bool
     */
    public bool $requireInsurance;

    /**
     * @var float
     */
    public float $insurancePrice;

    /**
     * @var bool
     */
    public bool $arrivalAddress;

    /**
     * @var bool
     */
    public bool $derivalAddress;

    /**
     * @var \SergeevPasha\Pecom\DTO\Service
     */
    public Service $derivalService;

    /**
     * @var \SergeevPasha\Pecom\DTO\Service
     */
    public Service $arrivalService;

    /**
     * @var \SergeevPasha\Pecom\DTO\Collection\CargoCollection;
     */
    public CargoCollection $cargo;

    /**
     * From Array.
     *
     * @param array<mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self([
            'arrivalCityId'       => (int) $data['arrival_city_id'],
            'derivalCityId'       => (int) $data['derival_city_id'],
            'arrivalOpenCar'      => isset($data['arrival_open_car']) ? (bool) $data['arrival_open_car'] : false,
            'derivalOpenCar'      => isset($data['derival_open_car']) ? (bool) $data['derival_open_car'] : false,
            'arrivalDistanceType' => isset($data['arrival_distance_type']) ? (int) $data['arrival_distance_type'] : 0,
            'derivalDistanceType' => isset($data['derival_distance_type']) ? (int) $data['derival_distance_type'] : 0,
            'oneDayDelivery'      => isset($data['one_day_delivery']) ? (bool) $data['one_day_delivery'] : false,
            'isShop'              => isset($data['is_shop']) ? (bool) $data['is_shop'] : false,
            'payDate'             => $data['pay_date'],
            'requireInsurance'    => isset($data['require_insurance']) ? (bool) $data['require_insurance'] : false,
            'insurancePrice'      => isset($data['insurance_price']) ? (float) $data['insurance_price'] : 0.00,
            'arrivalAddress'      => (bool) $data['arrival_address'],
            'derivalAddress'      => (bool) $data['derival_address'],
            'derivalService'      => isset($data['derival_service']) ?
                                        Service::fromArray($data['derival_service']) :
                                        Service::fromArray([]),
            'arrivalService'      => isset($data['arrival_service']) ?
                                        Service::fromArray($data['arrival_service']) :
                                        Service::fromArray([]),
            'cargo'               => CargoCollection::fromArray($data['cargo'])
        ]);
    }
}
