<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $code = 400;

    public function __construct(string $message = 'Insufficient stock available', int $code = 400)
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

        return back()->withErrors(['stock' => $this->getMessage()]);
    }
}
