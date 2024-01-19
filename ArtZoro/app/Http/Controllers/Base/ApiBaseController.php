<?php

namespace App\Http\Controllers\Base;

use App\Enums\SupportCurrency;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ApiBaseController extends Controller
{
    protected $statusCode = JsonResponse::HTTP_OK;

    public function sendSuccess($data = [], $message = '')
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $this->statusCode);
    }

    public function sendError($message = '', $code = JsonResponse::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    public function validationError(array $errors, $code = JsonResponse::HTTP_BAD_REQUEST)
    {
        throw ValidationException::withMessages($errors);
    }

    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function currencyIsValidCurrency($currency)
    {
        return SupportCurrency::hasValue($currency);
    }

    protected function validateOrderRequest(array $data)
    {
        $validator = validator($data, [
            'description' => 'required|string',
            'currency' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, SupportCurrency::getValidCurrencies())) {
                        $fail($attribute . ' is not a valid currency.');
                    }
                }
            ],
            'total' => 'required|numeric',
            'payment_method' => 'required|in:stripe,paypal,payoneer,orange_momo,mtn_momo',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }


}
