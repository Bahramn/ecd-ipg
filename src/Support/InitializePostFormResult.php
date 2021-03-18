<?php

namespace Bahramn\EcdIpg\Support;

use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InitializePostFormResult implements InitializeResultInterface
{
    private string $actionURL;
    private array $formData;
    private string $token;

    /**
     * InitializePostFormResult constructor.
     *
     * @param string $token
     * @param string $actionURL
     * @param array $formData
     */
    public function __construct(string $token, string $actionURL, array $formData)
    {
        $this->token = $token;
        $this->formData = $formData;
        $this->actionURL = $actionURL;
    }

    /**
     * Returns the type of the result.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'postForm';
    }

    /**
     * Returns the URL that we should redirect the user to.
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this->actionURL;
    }

    /**
     * Returns the response corresponding to this result.
     *
     * @return Factory|Response|View
     */
    public function getResponse()
    {
        return view('ecd-ipg::ecd-gateway.post-form', [
            'data' => $this,
        ]);
    }

    /**
     * @return array
     */
    public function getFormData(): array
    {
        return $this->formData;
    }

    /**
     * Returns the additional data that may the initialize result has.
     *
     * @return array
     */
    public function getAdditionalData(): array
    {
        return array_map(fn ($value, $key) => [
                'key' => $key,
                'value' => $value,
            ], $this->formData, array_keys($this->formData));
    }

    /**
     * @return string
     */
    public function getGateWayTransactionToken(): string
    {
        return $this->token;
    }
}
