<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use ValueError;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        [$data, $statusCode] = match(true) {
            $exception instanceof ValidationException
                => [['errors' => $exception->getErrors()], Response::HTTP_UNPROCESSABLE_ENTITY],

            $exception instanceof NotFoundException
                => [['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND],

            $exception instanceof ValueError
                => [['error' => 'Invalid value provided'], Response::HTTP_BAD_REQUEST],

            $exception instanceof HttpExceptionInterface
                => [['error' => $exception->getMessage()], $exception->getStatusCode()],

            // default
                // => [['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR]

                default
                        => [
                            [
                                'error' => $exception->getMessage(),
                                'class' => get_class($exception),
                                'line'  => $exception->getLine(),
                                'file'  => $exception->getFile(),
                            ],
                            Response::HTTP_INTERNAL_SERVER_ERROR
                        ]
        };

        $event->setResponse(new JsonResponse($data, $statusCode));
    }
}
