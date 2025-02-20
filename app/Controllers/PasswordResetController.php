<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\ForgotPasswordRequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PasswordResetController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
    ) {
    }

    /**
     * @param  Response  $response
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function showForgotPasswordForm(Response $response): Response
    {
        return $this->twig->render($response, 'auth/forgot_password.twig');
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     *
     * @return Response
     */
    public function handleForgotPasswordRequest(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(ForgotPasswordRequestValidator::class)->validate(
            $request->getParsedBody(),
        );

        return $response;
    }
}