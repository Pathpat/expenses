<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config;
use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class ValidateSignatureMiddleware implements MiddlewareInterface
{

    public function __construct(
        private readonly Config $config,
    ) {
    }

    /**
     * @param  ServerRequestInterface  $request
     * @param  RequestHandlerInterface  $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri               = $request->getUri();
        $queryParams       = $request->getQueryParams();
        $originalSignature = $queryParams['signature'] ?? '';
        $expiration        = (int) ($queryParams['expiration'] ?? 0);

        unset($queryParams['signature']);

        $url       = (string) $uri->withQuery(http_build_query($queryParams));
        $signature = hash_hmac('sha256', $url, $this->config->get('app_key'));

        if ($expiration <= time() || ! hash_equals($signature, $originalSignature)) {
            throw new \RuntimeException('Failed to verify signature');
        }

        return $handler->handle($request);
    }
}