<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Category;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\RequestValidators\UpdateCategoryRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CategoryController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'categories/index.twig');
    }

    /**
     * @throws ORMException
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->categoryService->create($data['name'], $request->getAttribute('user'));

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    /**
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->categoryService->delete((int)$args['id']);

        return $response;
    }

    /**
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryService->getById((int)$args['id']);

        if (!$category) {
            return $response->withStatus(404);
        }

        $data = ['id' => $category->getId(), 'name' => $category->getName()];

        return $this->responseFormatter->asJson($response, $data);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws \JsonException
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateCategoryRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );
        $category = $this->categoryService->getById((int)$data['id']);

        if (!$category) {
            return $response->withStatus(404);
        }

        $this->categoryService->update($category, $data['name']);

        return $this->responseFormatter->asJson($response, $data);
    }

    /**
     * @param  Request  $request
     * @param  Response  $response
     * @return Response
     * @throws \JsonException
     * @throws \Exception
     */
    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParameters($request);
        $categories = $this->categoryService->getPaginatedCategories($params);

        $transformer = function (Category $category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
                'updatedAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
            ];
        };

        $totalCategories = count($categories);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array)$categories->getIterator()),
            $params->draw,
            $totalCategories,
        );
    }
}