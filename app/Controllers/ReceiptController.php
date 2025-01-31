<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UploadReceiptRequestValidator;
use App\Services\ReceiptService;
use App\Services\TransactionService;
use Doctrine\ORM\Exception\ORMException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Random\RandomException;

class ReceiptController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ReceiptService $receiptService,
        private readonly TransactionService $transactionService,
    ) {
    }

    /**
     * @param  Request   $request
     * @param  Response  $response
     * @param  array     $args
     *
     * @return Response
     * @throws FilesystemException
     * @throws ORMException
     * @throws RandomException
     */
    public function store(
        Request $request,
        Response $response,
        array $args
    ): Response {
        /** @var UploadedFileInterface $file */
        $file = $this->requestValidatorFactory->make(
            UploadReceiptRequestValidator::class
        )->validate($request->getUploadedFiles())['receipt'];
        $filename = $file->getClientFilename();

        $id = (int) $args['id'];

        if (! $id || ! ($transaction = $this->transactionService->getById($id))) {
            return $response->withStatus(404);
        }

        $randomFilename = bin2hex(random_bytes(25));

        $this->filesystem->write(
            'receipts/'.$randomFilename,
            $file->getStream()->getContents()
        );

        $this->receiptService->create($transaction, $filename, $randomFilename);

        return $response;
    }
}