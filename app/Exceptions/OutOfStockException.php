<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    protected $code = 400;

    public function __construct(string $message = 'Product is out of stock', int $code = 400)
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
