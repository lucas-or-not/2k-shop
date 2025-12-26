<?php

namespace App\Exceptions;

use Exception;

class LowStockException extends Exception
{
    protected $code = 200; // Not an error, just a notification

    public function __construct(string $message = 'Product stock is running low', int $code = 200)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $this->getMessage(),
                'status_code' => $this->getCode(),
            ], $this->getCode());
        }

        return back()->with('warning', $this->getMessage());
    }
}
