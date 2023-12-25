<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionMethod;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Resources\ReflectionHelper;
use function app;

class ControllerToGraphqlFieldsMapper
{

    public static function mapMutationFields(string $controllerMetadata): array
    {
        $mutationFields = [];
        $controllerReflection = new ReflectionClass($controllerMetadata);
        $entityMetadata = ReflectionHelper::getAttributeArgument($controllerReflection, GraphqlMapableController::class,
                        'entity');
        $responseTypeMetadata = ReflectionHelper::getAttributeArgument($controllerReflection,
                        GraphqlMapableController::class, 'responseType') ?? $entityMetadata;

        foreach (ReflectionHelper::getPublicMethodsWithAttribute($controllerReflection, Mutation::class) as $methodReflection) {
            $args = [];
            if (ReflectionHelper::methodHasParameter($methodReflection, 'id')) {
                $args = [
                    ...$args,
                    'id' => Type::id()
                ];
            }
            if (ReflectionHelper::methodHasParameterClass($methodReflection, InputRequest::class)) {
                $args = [
                    ...$args,
                    ...DoctrineEntityToGraphqlFieldMapper::mapInputFields($entityMetadata),
                ];
            }

//            $mutationFields[static::generateMethodName($methodReflection, $controllerReflection)] = [
            $mutationFields[$methodReflection->getName()] = [
                'type' => TypeRegistry::objectType($responseTypeMetadata),
//                'args' => DoctrineEntityToGraphqlFieldMapper::mapInputFields($entityMetadata),
                'args' => $args,
                'resolve' => function ($root, $args, AppContext $app) use ($controllerMetadata, $methodReflection) {
                    $methodArguments = static::getMethodArguments($methodReflection, $app, $args);
                    return $methodReflection->invokeArgs(app($controllerMetadata), $methodArguments);
                },
            ];
        }
        return $mutationFields;
    }

    public static function mapQueryFields(string $controllerMetadata): array
    {
        $queryFields = [];
        $controllerReflection = new ReflectionClass($controllerMetadata);
        $entityMetadata = ReflectionHelper::getAttributeArgument($controllerReflection, GraphqlMapableController::class,
                        'entity');
        $responseTypeMetadata = ReflectionHelper::getAttributeArgument($controllerReflection,
                        GraphqlMapableController::class, 'responseType') ?? $entityMetadata;

        foreach (ReflectionHelper::getPublicMethodsWithAttribute($controllerReflection, Query::class) as $methodReflection) {
            $args = [];

            $inputTypeMetadata = ReflectionHelper::getAttributeArgument($methodReflection, Query::class, 'inputType');
            if ($inputTypeMetadata) {
                $inputTypeObject = new \ReflectionClass($inputTypeMetadata);
                if ($inputTypeObject->isSubclassOf(GraphqlInputType::class)) {
                    $args = [
                        ...$args,
                        ...$inputTypeObject->newInstance()->fieldDefinition(),
                    ];
                }
            }

            if (ReflectionHelper::methodHasParameter($methodReflection, 'id')) {
                $args = [
                    ...$args,
                    'id' => Type::id()
                ];
            }

            if (ReflectionHelper::getAttributeArgument($methodReflection, Query::class, 'responseWrapper') === Query::PAGINATION_RESPONSE_WRAPPER) {
                $responseType = TypeRegistry::paginationType($responseTypeMetadata);
//                $responseType = new Pagination(TypeRegistry::objectType($responseTypeMetadata));
                $args = [
                    ...$args,
                    ...InputListSchema::paginationListSchema(),
                ];
            } elseif (ReflectionHelper::getAttributeArgument($methodReflection, Query::class, 'responseWrapper') === Query::LIST_RESPONSE_WRAPPER) {
                $responseType = Type::listOf(TypeRegistry::objectType($responseTypeMetadata));
                $args = [
                    ...$args,
                    ...InputListSchema::allListSchema(),
                ];
            } else {
                $responseType = TypeRegistry::objectType($responseTypeMetadata);
            }

//            $queryFields[static::generateMethodName($methodReflection, $controllerReflection)] = [
            $queryFields[$methodReflection->getName()] = [
                'type' => $responseType,
                'args' => $args,
                'resolve' => function ($root, $args, AppContext $app) use ($controllerMetadata, $methodReflection) {
                    $methodArguments = static::getMethodArguments($methodReflection, $app, $args);
                    return $methodReflection->invokeArgs(app($controllerMetadata), $methodArguments);
                },
            ];
        }
        return $queryFields;
    }

    //
//    protected static function generateMethodName(ReflectionMethod $methodReflection,
//            \ReflectionClass $controllerReflection): string
//    {
//        $entityMetadata = ReflectionHelper::getAttributeArgument($controllerReflection, GraphqlMapableController::class,
//                        'entity');
//        $entityName = (new \ReflectionClass($entityMetadata))->getShortName();
//        return $methodReflection->getName() . $entityName;
//    }

    protected static function getMethodArguments(ReflectionMethod $methodReflection, AppContext $app, $args): array
    {
        $methodArguments = [];
        foreach (ReflectionHelper::getMethodParameters($methodReflection) as $parameter) {
            if ($parameter->getType()->isBuiltin()) {
                $methodArguments[] = $args[$parameter->getName()] ?? $app->getParameter($parameter->getName());
            } elseif ($parameter->getType()->getName() == InputRequest::class) {
                $methodArguments[] = new GraphqlInputRequest($args);
            } else {
                $methodArguments[] = app($parameter->getType()->getName());
            }
        }
        return $methodArguments;
    }
}
