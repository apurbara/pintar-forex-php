<?php

namespace Resources\Infrastructure\GraphQL;

use ErrorException;
use Exception;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Illuminate\Http\Request;
use Resources\Exception\RegularException;
use function response;

/** @var Request $request */
/** @var Schema $schema */
/** @var $appContext */
// Disable default PHP error reporting - we have better one for debug mode (see below)
ini_set('display_errors', 0);
$debug = DebugFlag::NONE;
if ($request->query('debug')) {
    set_error_handler(function ($severity, $message, $file, $line) use (&$phpErrors) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    $debug = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
}

//enable complexiti rule, particularly to prevent circular reference query
$rule = new \GraphQL\Validator\Rules\QueryComplexity(400);
\GraphQL\Validator\DocumentValidator::addRule($rule);

try {
    $data = $request->input();
    if (!$data) {
        $data = $request->query();
    }
    $data += ['query' => null, 'variables' => null];

    if (empty($data["query"])) {
        $data["query"] = '{hello}';
    }
    $result = GraphQL::executeQuery(
                    $schema, $data['query'], null, $appContext, (array) $data['variables']
    );
    $output = $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
    if (!empty($output['errors'])) {
        throw RegularException::badRequest($output['errors'][0]['message']);
    }
    $response = $output['data'];
    $httpStatus = 200;
} catch (RegularException $error) {
    $httpStatus = $error->getCode();
    $response['errors'] = [
        $error->getErrorDetail(),
    ];
//    throw $error;
} catch (Exception $error) {
    $httpStatus = $error->getCode();
    $response['errors'] = [
        FormattedError::createFromException($error, $debug)
    ];
    throw $error;
}
return response()->json($response, $httpStatus, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
