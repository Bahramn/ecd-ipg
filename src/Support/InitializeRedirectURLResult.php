<?php

namespace Bahramn\EcdIpg\Support;

use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Illuminate\Http\RedirectResponse;


/**
 * @package Bahramn\EcdIpg\Payment
 */
class InitializeRedirectURLResult implements InitializeResultInterface
{
    private string $redirectURL;

    /**
     * InitializeRedirectURLResult constructor.
     * @param string $redirectURL
     */
    public function __construct(string $redirectURL)
    {
        $this->redirectURL = $redirectURL;
    }

    /**
     * Returns the type of the result.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'redirectURL';
    }

    /**
     * Returns the URL that we should redirect the user to.
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this->redirectURL;
    }

    /**
     * Returns the response corresponding to this result.
     *
     * @return RedirectResponse
     */
    public function getResponse(): RedirectResponse
    {
        return redirect()->to($this->getURL());
    }

    /**
     * Returns the additional data that may the initialize result has.
     *
     * @return array
     */
    public function getAdditionalData(): array
    {
        return [];
    }

    public function getGateWayTransactionToken(): string
    {
        return '';
    }
}
