<?php

declare(strict_types=1);

namespace SergeevPasha\Pecom\Http\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use SergeevPasha\Pecom\DTO\Delivery;
use Illuminate\Support\Facades\Validator;
use SergeevPasha\Pecom\Http\Requests\PecomDeliveryStatusRequest;
use SergeevPasha\Pecom\Libraries\PecomClient;
use Illuminate\Validation\ValidationException;
use SergeevPasha\Pecom\Http\Requests\PecomQueryCityRequest;
use SergeevPasha\Pecom\Http\Requests\PecomCalculatePriceRequest;

class PecomController
{
    /**
     * Pecom Client Instance.
     *
     * @var \SergeevPasha\Pecom\Libraries\PecomClient
     */
    private PecomClient $client;

    public function __construct(PecomClient $client)
    {
        $this->client = $client;
    }

    /**
     * Validate Pecom response
     *
     * @param array|null $data
     *
     * @throws \Exception
     * @return void
     */
    public function validateResponse(?array $data): void
    {
        $validator = Validator::make([], []);

        if (isset($data['error'])) {
            if (isset($data['error']['fields'])) {
                foreach ($data['error']['fields'] as $pecomFields) {
                    $messages = is_array($pecomFields['Value']) ? trim(implode('. ', $pecomFields['Value'])) : $pecomFields['Value'];
                    $validator->errors()->add($pecomFields['Key'], $messages);
                }
                throw new ValidationException($validator);
            }
            throw new Exception('Input data is invalid');
        }
    }

    /**
     * Query City.
     *
     * @api
     *
     * @param \SergeevPasha\Pecom\Http\Requests\PecomQueryCityRequest $request
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return \Illuminate\Http\JsonResponse
     */
    public function queryCity(PecomQueryCityRequest $request): JsonResponse
    {
        $data['data'] = $this->client->findCity($request->query('query'));
        return response()->json($data);
    }

    /**
     * Get city terminals.
     *
     * @api
     *
     * @param int $city
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCityTerminals(int $city): JsonResponse
    {
        $data['data'] = $this->client->getCityTerminals($city);
        return response()->json($data);
    }

    /**
     * Calculate delivery.
     *
     * @api
     *
     * @param \SergeevPasha\Pecom\Http\Requests\PecomCalculatePriceRequest $request
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateDeliveryPrice(PecomCalculatePriceRequest $request): JsonResponse
    {
        $data = $this->client->getPrice(Delivery::fromArray($request->all()));
        $this->validateResponse($data);
        return response()->json($data);
    }

    /**
     * Get cargo status by cargo codes.
     *
     * @param PecomDeliveryStatusRequest $request
     *
     * @return JsonResponse
     * @throws GuzzleException
     * @throws Exception
     * @api
     *
     */
    public function getCargoStatus(PecomDeliveryStatusRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->client->getOrderHistory($data['cargo_code']);
        return response()->json($result[0]['statuses']);
    }
}
