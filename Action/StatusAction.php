<?php

declare(strict_types=1);

namespace PayHelper\Payum\Mollie\Action;

use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Api\Types\SettlementStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model['subscription'])) {
            switch ($model['subscription']['status']) {
                case SubscriptionStatus::ACTIVE:
                    $request->markAuthorized();

                    break;
                case SubscriptionStatus::PENDING:
                    $request->markPending();

                    break;
                case SubscriptionStatus::CANCELED:
                    $request->markCanceled();

                    break;
                case SubscriptionStatus::COMPLETED:
                    $request->markCaptured();

                    break;
                case SubscriptionStatus::SUSPENDED:
                    $request->markSuspended();

                    break;
                default:
                    $request->markUnknown();

                    break;
            }

            return;
        }

        if (!isset($model['payment'])) {
            $request->markNew();

            return;
        }

        switch ($model['payment']['status']) {
            case PaymentStatus::OPEN:
                $request->markNew();

                break;
            case PaymentStatus::PAID:
                $request->markCaptured();

                break;
            case PaymentStatus::CANCELED:
                $request->markCanceled();

                break;
            case PaymentStatus::PENDING:
                $request->markPending();

                break;
            case PaymentStatus::FAILED:
                $request->markFailed();

                break;
            case SettlementStatus::PAIDOUT:
                $request->markPayedout();

                break;
            case PaymentStatus::EXPIRED:
                $request->markExpired();

                break;
            case RefundStatus::REFUNDED:
                $request->markRefunded();

                break;
            default:
                $request->markUnknown();

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
