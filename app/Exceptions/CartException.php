<?php

namespace App\Exceptions;

use Exception;

class CartException extends Exception
{
    protected $code = 400;

    public function __construct(string $message = 'Cart operation failed', int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->getMessage(),
                'code' => $this->getCode(),
            ], $this->getCode());
        }

        return back()->withErrors(['cart' => $this->getMessage()]);
    }
}
