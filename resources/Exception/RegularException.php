<?php

namespace Resources\Exception;

class RegularException extends \Exception
{

    protected $errorDetail;

    public function __construct(string $errorDetail, string $message = "", int $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->errorDetail = $errorDetail;
    }

    function getErrorDetail(): string
    {
        return $this->errorDetail;
    }

    public static function badRequest(string $errorDetail): RegularException
    {
        $message = "Bad Request";
        $code = 400;
        return new static($errorDetail, $message, $code);
    }

    public static function unauthorized(string $errorDetail): RegularException
    {
        $message = "Unauthorized";
        $code = 401;
        return new static($errorDetail, $message, $code);
    }

    public static function paymentRequired(string $errorDetail): RegularException
    {
        $message = "Payment Required";
        $code = 402;
        return new static($errorDetail, $message, $code);
    }

    public static function forbidden(string $errorDetail): RegularException
    {
        $message = "Forbidden";
        $code = 403;
        return new static($errorDetail, $message, $code);
    }

    public static function notFound(string $errorDetail): RegularException
    {
        $message = "Not Found";
        $code = 404;
        return new static($errorDetail, $message, $code);
    }

    public static function methodNotAllowed(string $errorDetail): RegularException
    {
        $message = "Method Not Allowed";
        $code = 405;
        return new static($errorDetail, $message, $code);
    }

    public static function notAcceptable(string $errorDetail): RegularException
    {
        $message = "Not Acceptable";
        $code = 406;
        return new static($errorDetail, $message, $code);
    }

    public static function requestTimeout(string $errorDetail): RegularException
    {
        $message = "Request Timeout";
        $code = 408;
        return new static($errorDetail, $message, $code);
    }

    public static function conflict(string $errorDetail): RegularException
    {
        $message = "Conflict";
        $code = 409;
        return new static($errorDetail, $message, $code);
    }

    public static function payloadTooLarge(string $errorDetail): RegularException
    {
        $message = "Payload Too Large";
        $code = 413;
        return new static($errorDetail, $message, $code);
    }

    public static function unsupportedMediaType(string $errorDetail): RegularException
    {
        $message = "Unsupported Media Type";
        $code = 415;
        return new static($errorDetail, $message, $code);
    }

    public static function tooManyRequests(string $errorDetail): RegularException
    {
        $message = "Too Many Request";
        $code = 429;
        return new static($errorDetail, $message, $code);
    }

    public static function internalServerError(string $errorDetail): RegularException
    {
        $message = "Internal Server Error";
        $code = 500;
        return new static($errorDetail, $message, $code);
    }

    public static function networkAuthenticationRequired(string $errorDetail): RegularException
    {
        $message = "Network Authentication Required";
        $code = 511;
        return new static($errorDetail, $message, $code);
    }

}
