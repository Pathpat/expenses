<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\TransactionData;
use App\Entity\Receipt;
use App\Entity\Transaction;
use App\RequestValidators\TransactionRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use App\Services\TransactionService;
use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TransactionController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly CategoryService $categoryService,
        private readonly EntityManagerServiceInterface $entityManagerService,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(Response $response): Response
    {
        return $this->twig->render(
            $response,
            'transactions/index.twig',
            ['categories' => $this->categoryService->getCategoryNames()]
        );
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     *
     * @return Response
     * @throws \DateMalformedStringException
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $transaction = $this->transactionService->create(
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            ),
            $request->getAttribute('user')
        );

        $this->entityManagerService->sync($transaction);

        return $response;
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     * @param  array     $args
     *
     * @return Response
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function delete(Response $response, Transaction $transaction): Response
    {
        $this->entityManagerService->delete($transaction, true);

        return $response;
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     * @param  array     $args
     *
     * @return Response
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \JsonException
     */
    public function get(Response $response, Transaction $transaction): Response
    {
        $data = [
            'id'          => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'amount'      => $transaction->getAmount(),
            'date'        => $transaction->getDate()->format('Y-m-d\TH:i'),
            'category'    => $transaction->getCategory()->getId(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     * @param  array     $args
     *
     * @return Response
     * @throws \DateMalformedStringException
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function update(Request $request, Response $response, Transaction $transaction): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->entityManagerService->sync($this->transactionService->update(
            $transaction,
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            )
        ));


        return $response;
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     *
     * @return Response
     * @throws \JsonException
     */
    public function load(Request $request, Response $response): Response
    {
        $params       = $this->requestService->getDataTableQueryParameters($request);
        $transactions = $this->transactionService->getPaginatedTransactions($params);
        $transformer  = function (Transaction $transaction) {
            return [
                'id'          => $transaction->getId(),
                'description' => $transaction->getDescription(),
                'amount'      => $transaction->getAmount(),
                'date'        => $transaction->getDate()->format('m/d/Y g:i A'),
                'category'    => $transaction->getCategory()?->getName(),
                'wasReviewed' => $transaction->wasReviewed(),
                'receipts'    => $transaction->getReceipts()->map(fn(Receipt $receipt) => [
                    'name'        => $receipt->getFilename(),
                    'id'          => $receipt->getId(),
                ])->toArray(),
            ];
        };

        $totalTransactions = count($transactions);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $transactions->getIterator()),
            $params->draw,
            $totalTransactions
        );
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     * @param  array     $args
     *
     * @return Response
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function toggleReviewed(Response $response, Transaction $transaction): Response
    {
        $this->transactionService->toggleReviewed($transaction);
        $this->entityManagerService->sync();
        return $response;
    }
}