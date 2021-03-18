<?php

namespace Bahramn\EcdIpg\Support\Interfaces;

use Illuminate\Http\Response;
use Illuminate\View\View;

interface InitializeResultInterface
{
    /**
     * Returns the type of the result.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns the URL that we should redirect the user to.
     *
     * @return string
     */
    public function getURL(): string;

    /**
     * Returns the response corresponding to this result.
     *
     * @return Response|View
     */
    public function getResponse();

    /**
     * Returns the additional data that may the initialize result has.
     *
     * @return array
     */
    public function getAdditionalData(): array;

    /**
     * Returns the gateway unique id for transaction has initialized.
     *
     * @return string
     */
    public function getGateWayTransactionToken(): string;
}
