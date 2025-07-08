<?php

namespace App\Services;

use Exception;

class SatimService
{
    private $username;
    private $password;
    private $terminalId;
    private $testMode;
    private $udfMappings = [];

    public function __construct()
    {
        $this->username = config('services.satim.username');
        $this->password = config('services.satim.password');
        $this->terminalId = config('services.satim.terminal_id');
        $this->testMode = config('services.satim.test_mode', true);
    }

    public function registerPayment(float $amount, string $orderNumber, int $userId, string $type = 'order')
    {
        try {
            $baseUrl = $this->testMode
                ? 'https://test.satim.dz/payment/rest/register.do'
                : 'https://satim.dz/payment/rest/register.do';

            $params = [
                'currency' => '012',
                'amount' => $amount * 100,
                'language' => 'AR',
                'orderNumber' => $orderNumber,
                'userName' => $this->username,
                'password' => $this->password,
                'returnUrl' => config('payment.satim.return'),
                'failUrl' => config('payment.satim.fail'),
                'jsonParams' => json_encode(array_merge([
                    'force_terminal_id' => $this->terminalId,
                    'udf1' => (string)$userId,
                    'udf5' => $type
                ], $this->udfMappings))
            ];
            $response = $this->makeApiCall($baseUrl, $params);

            if (!isset($response['orderId'])) {
                throw new Exception('Invalid SATIM response: ' . json_encode($response));
            }

            return [
                'order_id' => $response['orderId'],
                'payment_url' => $response['formUrl'],
                'raw_response' => $response
            ];
        } catch (Exception $e) {
            logger()->error('SATIM payment registration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPaymentStatus(string $orderId)
    {
        try {
            $baseUrl = $this->testMode
                ? 'https://test.satim.dz/payment/rest/getOrderStatus.do'
                : 'https://satim.dz/payment/rest/getOrderStatus.do';

            $params = [
                'userName' => $this->username,
                'password' => $this->password,
                'orderId' => $orderId,
            ];

            $response = $this->makeApiCall($baseUrl, $params, false);

            if (!isset($response['OrderStatus'])) {
                throw new Exception('Invalid SATIM status response: ' . json_encode($response));
            }

            return [
                'status' => $response['OrderStatus'],
                'raw_response' => $response
            ];
        } catch (Exception $e) {
            logger()->error('SATIM status check failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function makeApiCall(string $url, array $params, bool $isPost = true)
    {
        $ch = curl_init();

        if ($isPost) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response);
        }

        return $responseData;
    }
}
