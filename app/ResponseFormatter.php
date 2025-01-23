<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;

class ResponseFormatter
{

    /**
     * @param  ResponseInterface  $response
     * @param  mixed  $data
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function asJson(
        ResponseInterface $response,
        mixed $data,
        int $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    ): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');

        $response->getBody()->write(
            json_encode($data, $flags)
        );

        return $response;
    }

    /**
     * @param  ResponseInterface  $response
     * @param  array  $data
     * @param  int  $draw
     * @param  int  $total
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function asDataTable(ResponseInterface $response, array $data, int $draw, int $total): ResponseInterface
    {
        return $this->asJson($response, [
            'data' => $data,
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ]);
    }
}