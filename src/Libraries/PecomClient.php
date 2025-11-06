<?php

declare(strict_types=1);

namespace SergeevPasha\Pecom\Libraries;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use SergeevPasha\Pecom\DTO\Delivery;
use SergeevPasha\Pecom\DTO\Collection\CargoCollection;
use SergeevPasha\Pecom\DTO\PecomTrack;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class PecomClient
{
    /**
     * Pecom User.
     *
     * @var string
     */
    private string $user;

    /**
     * Pecom App key.
     *
     * @var string
     */
    private string $key;

    /**
     * PecomClient constructor.
     *
     * @param string|null $user
     * @param string|null $key
     *
     * @throws \Exception
     */
    public function __construct(?string $user, ?string $key)
    {
        if (!$user || !$key) {
            throw new Exception(trans('pecom::messages.missed_auth_data'));
        }
        $this->user = $user;
        $this->key  = $key;
    }

    /**
     * Send request to Pecom API.
     *
     * @param string       $path
     * @param array<mixed> $params
     * @param string       $method
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     * @return array<mixed>|null
     */
    public function request(string $path, array $params = [], string $method = 'POST'): ?array
    {
        try {
            $options  = [
                'auth'        => [
                    $this->user,
                    $this->key
                ],
                'json'        => $params,
                'http_errors' => false,
            ];
            $client   = new \GuzzleHttp\Client();
            $response = $client->request($method, $path, $options);
        } catch (Exception $e) {
            throw new Exception(trans('pecom::messages.connection_fail'));
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get extended city data by query string.
     *
     * @param string $query
     *
     * @return array<mixed>
     */
    public function getExtendedCityData(string $query): array
    {
        $contents = json_decode(file_get_contents(__DIR__ . '/../data/pecom.json'));
        $city     = [];
        foreach ($contents->branches as $region) {
            $findCity = array_filter(
                $region->cities,
                fn($regionCity) => strpos(mb_strtolower($regionCity->title), mb_strtolower($query)) !== false
            );
            if (!empty($findCity)) {
                if (is_array($findCity)) {
                    foreach ($findCity as $foundCity) {
                        $city[] = $foundCity;
                    }
                }
            }
        }
        usort($city, fn($a, $b) => $a->bitrixId > $b->bitrixId ? 1 : 0);
        return (array) $city;
    }

    /**
     * Find a city by query string.
     *
     * @param string $query
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array<mixed>
     */
    public function findCity(string $query): array
    {
        $data = $this->request(
            'https://kabinet.pecom.ru/api/v1/branches/findbytitle',
            [
                'title' => $query,
                'exact' => false
            ]
        );
        if ($data) {
            return $data['items'];
        } else {
            return [];
        }
    }

    /**
     * Get basic cargo status information by cargo codes.
     *
     * @param string $cargoCode
     * @return PecomTrack
     * @throws GuzzleException
     * @throws UnknownProperties
     */
    public function findByTrackNumber(string $cargoCode): PecomTrack
    {
        $data = $this->request(
            'https://kabinet.pecom.ru/api/v1/cargos/basicstatus',
            [
                'cargoCodes' => [$cargoCode],
            ]
        );
        return PecomTrack::fromArray($data);
    }

    /**
     * Get Orders History.
     *
     * @param array $cargoCodes
     * @return array
     * @throws GuzzleException
     */
    public function getOrdersHistory(array $cargoCodes): array
    {
        return $this->request('https://kabinet.pecom.ru/api/v1/cargos/statusfullhistory', [
            'cargoCodes' => $cargoCodes
        ]);
    }

    /**
     * Get Order History.
     *
     * @param string $cargoCode
     * @return array
     * @throws GuzzleException
     */
    public function getOrderHistory(string $cargoCode): array
    {
        return $this->getOrdersHistory([$cargoCode]);
    }
    /**
     * Find all available city terminals
     *
     * @param int $cityId
     *
     * @return array<mixed>|null
     */
    public function getCityTerminals(int $cityId): ?array
    {
        $contents = json_decode(file_get_contents(__DIR__ . '/../data/pecom.json'));
        $city     = [];
        foreach ($contents->branches as $region) {
            $findCity = array_filter($region->cities, fn($regionCity) => (int) $regionCity->bitrixId === $cityId);
            if (!empty($findCity)) {
                $city = $findCity;
                break;
            }
        }
        $city      = array_pop($city);
        $terminals = [];
        if ($city->divisions) {
            foreach ($city->divisions as $division) {
                foreach ($contents->branches as $region) {
                    $findCity = array_filter(
                        $region->cities,
                        fn($regionCity) => (int) $regionCity->bitrixId === $cityId
                    );
                    if (!empty($findCity)) {
                        $found       = array_filter(
                            $region->divisions,
                            fn($regionDivision) => $regionDivision->id === $division
                        );
                        $terminals[] = array_pop($found);
                    }
                }
            }
        }
        return (array) $terminals;
    }

    /**
     * Get calculated price.
     *
     * @param \SergeevPasha\Pecom\DTO\Delivery $delivery
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array<mixed>|null
     */
    public function getPrice(Delivery $delivery): ?array
    {
        $request = [
            'senderCityId'         => $delivery->derivalCityId,
            'receiverCityId'       => $delivery->arrivalCityId,
            'isOpenCarSender'      => $delivery->derivalOpenCar,
            'senderDistanceType'   => $delivery->derivalDistanceType,
            'isDayByDay'           => $delivery->oneDayDelivery,
            'isOpenCarReceiver'    => $delivery->arrivalOpenCar,
            'receiverDistanceType' => $delivery->arrivalDistanceType,
            'isHyperMarket'        => $delivery->isShop,
            'calcDate'             => $delivery->payDate,
            'isInsurance'          => $delivery->requireInsurance,
            'isInsurancePrice'     => $delivery->insurancePrice,
            'isPickUp'             => $delivery->derivalAddress,
            'isDelivery'           => $delivery->arrivalAddress,
            'pickupServices'       => [
                'isLoading'        => $delivery->arrivalService->enabled,
                'floor'            => $delivery->arrivalService->floor,
                'carryingDistance' => $delivery->arrivalService->distance,
                'isElevator'       => $delivery->arrivalService->hasElevator,
            ],
            'deliveryServices'     => [
                'isLoading'        => $delivery->derivalService->enabled,
                'floor'            => $delivery->derivalService->floor,
                'carryingDistance' => $delivery->derivalService->distance,
                'isElevator'       => $delivery->derivalService->hasElevator,
            ],
            'cargos'               => $this->buildCargo($delivery->cargo)
        ];
        return $this->request('https://kabinet.pecom.ru/api/v1/calculator/calculateprice', $request);
    }

    /**
     * Build Cargo Array for the request
     *
     * @param \SergeevPasha\Pecom\DTO\Collection\CargoCollection $cargoCollection
     *
     * @return array<mixed>
     */
    private function buildCargo(CargoCollection $cargoCollection): array
    {
        $result = [];
        foreach ($cargoCollection as $cargo) {
            $result[] = [
                'length'                => $cargo->length,
                'width'                 => $cargo->width,
                'height'                => $cargo->height,
                'volume'                => $cargo->volume,
                'maxSize'               => $cargo->maxSize,
                'isHP'                  => $cargo->protectivePackage,
                'sealingPositionsCount' => $cargo->totalSealingPositions,
                'weight'                => $cargo->weight,
                'overSize'              => $cargo->oversized,
            ];
        }
        return $result;
    }
}
