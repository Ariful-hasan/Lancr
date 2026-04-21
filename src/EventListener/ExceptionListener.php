<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\AccessDeniedException;
use App\Exception\BudgetExceededException;
use App\Exception\InvalidRoleException;
use App\Exception\InvalidStatusTransitionException;
use App\Exception\NotFoundException;
use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityNotFoundException;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use ValueError;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __construct(
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        [$data, $statusCode] = match(true) {
            // ── Validation & Data Input ───────────────────────────────────
            $exception instanceof ValidationException
                => [['errors' => $exception->getErrors()], Response::HTTP_UNPROCESSABLE_ENTITY],

            $exception instanceof ValueError
                => [['error' => 'Invalid value provided'], Response::HTTP_BAD_REQUEST],

            // ── Doctrine / SQL Exceptions ─────────────────────────────────
            $exception instanceof UniqueConstraintViolationException
                => [['error' => 'This record already exists.'], Response::HTTP_CONFLICT],

            $exception instanceof ForeignKeyConstraintViolationException
                => [['error' => 'This record is in use and cannot be modified.'], Response::HTTP_CONFLICT],

            $exception instanceof EntityNotFoundException,
            $exception instanceof NotFoundException
                => [['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND],

            // ── Custom Business Exceptions ────────────────────────────────
            $exception instanceof InvalidStatusTransitionException
                => [['error' => $exception->getMessage()], Response::HTTP_CONFLICT],

            $exception instanceof BudgetExceededException,
            $exception instanceof InvalidRoleException
                => [['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY],

            // ── Security Exceptions ───────────────────────────────────────
            $exception instanceof AccessDeniedException,
            $exception instanceof AccessDeniedHttpException
                => [['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN],

            $exception instanceof UnauthorizedException
                => [['error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED],

            // ── Symfony / Generic Exceptions ──────────────────────────────
            $exception instanceof HttpExceptionInterface
                => [['error' => $exception->getMessage()], $exception->getStatusCode()],

            $exception instanceof LogicException
                => [['error' => $exception->getMessage()], Response::HTTP_CONFLICT],

            default
                => [['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR]
        };

        // Add debug information only if kernel.debug is true (APP_DEBUG=1)
        if ($this->debug) {
            $data['debug'] = [
                'message' => $exception->getMessage(),
                'class'   => get_class($exception),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => explode("\n", $exception->getTraceAsString()),
            ];
        }

        $event->setResponse(new JsonResponse($data, $statusCode));
    }
}
