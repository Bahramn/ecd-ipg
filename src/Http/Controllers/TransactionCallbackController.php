<?php

namespace Bahramn\EcdIpg\Http\Controllers;

use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentGatewayException;
use Bahramn\EcdIpg\Exceptions\TransactionHasBeenAlreadyFailedException;
use Bahramn\EcdIpg\Exceptions\TransactionHasBeenAlreadyPaidException;
use Bahramn\EcdIpg\Models\Transaction;
use Bahramn\EcdIpg\Payment\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

/**
 * @package Bahramn\EcdIpg\Http\Controller
 */
class TransactionCallbackController extends Controller
{

    private Payment $payment;
    private UrlGenerator $urlGenerator;

    public function __construct(Payment $payment, UrlGenerator $urlGenerator)
    {
        $this->payment = $payment;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string  $gateway
     * @param Request $request
     * @return RedirectResponse
     */
    public function __invoke(string $gateway, Request $request): RedirectResponse
    {
        $success = false;
        try {
            $transaction = $this->payment
                ->setGatewayName($gateway)
                ->readyConfirmation($request->input('transaction_id'))
                ->confirm();

            $success = true;
        } catch (TransactionHasBeenAlreadyPaidException $e) {
            $transaction = $e->getTransaction();
            $success = true;
        } catch (TransactionHasBeenAlreadyFailedException $e) {
            $transaction = $e->getTransaction();
        } catch (PaymentConfirmationFailedException | PaymentGatewayException $e) {
            $transaction = null;
        };

        return redirect(
            $this->getResultRedirectUrl($success, $transaction)
        );
    }

    /**
     * @param bool             $success
     * @param Transaction|null $transaction
     * @return string
     */
    private function getResultRedirectUrl(bool $success, ?Transaction $transaction): string
    {
        $key = $success ? 'success_redirect_url' : 'failed_redirect_url';
        $params = $transaction ? [
            config('ecd-ipg.after_payment.transaction_uuid_param_name') => $transaction->uuid,
            config('ecd-ipg.after_payment.payable_id_param_name') => $transaction->payable->getUniqueId(),
        ] : [];

        return $this->urlGenerator->to(config('ecd-ipg.after_payment.' . $key), $params);
    }
}
