<?php

namespace Tests\Http\GraphQL;

use Illuminate\Testing\TestResponse;
use Tests\Http\HttpTestCase;

abstract class GraphqlTestCase extends HttpTestCase
{

    abstract protected function graphqlUri(): string;

    protected string $graphqlQuery = <<<_QUERY
query {
    hello, write test query here
}
_QUERY;
    protected array $graphqlVariables = [];
    protected array $graphqlOperation = [];
    protected array $cursorLimit = [
        'pageSize' => 20,
        'cursor' => null,
        'orders' => [],
    ];
    protected $offsetLimit = [
        'pageSize' => 20,
        'page' => 1,
        'orders' => [],
    ];
    protected array $filters = [];
    protected array $keywordSearch = [];
    protected TestResponse $response;

    protected function postGraphqlRequest($token = [], $debugResponse = false)
    {
        $graphqlRequest = [];
        $graphqlRequest['query'] = $this->graphqlQuery;
        if (isset($this->graphqlVariables)) {
            $graphqlRequest["variables"] = $this->graphqlVariables;
        }
        if (!empty($this->graphqlOperation)) {
            $graphqlRequest["operations"] = $this->graphqlOperation;
        }
        $this->response = $this->post($this->graphqlUri(), $graphqlRequest, $token);

        if ($debugResponse) {
            var_dump($this->response->json());
        }
    }
    
    //
    protected function getPaginationInput()
    {
        return [
            'keywordSearch' => $this->keywordSearch,
            'filters' => $this->filters,
            'cursorLimit' => $this->cursorLimit,
            'offsetLimit' => $this->offsetLimit,
        ];
    }

    //
//    protected function getNullableFilterValue($filter)
//    {
//        return $filter ? $filter : 'null';
//    }
//
//    protected function getGraphqlNullableStringInput($input): string
//    {
//        return $input ? '"' . $input . '"' : "null";
//    }

    //
    protected function seeStatusCode(int $status)
    {
        return $this->response->assertStatus($status);
    }

    protected function seeJsonContains($data)
    {
        return $this->response->assertJsonFragment($data);
    }

    protected function seeJsonDoesntContains($data)
    {
        return $this->response->assertJsonMissing($data);
    }

    protected function seeInDatabase($table, $data)
    {
        $this->assertDatabaseHas($table, $data);
    }

    protected function doesntSeeInDatabase($table, $data)
    {
        $this->assertDatabaseMissing($table, $data);
    }

    //
    protected function printGraphQlErrorTrace()
    {
        var_dump($this->response->dump());
    }

    protected function printApiSpesification()
    {
        echo ' uri: ';
        echo $this->graphqlUri();
        echo ' payload: ';
        var_dump(json_encode([
            'query' => $this->graphqlQuery,
            'variables' => $this->graphqlVariables,
        ], JSON_UNESCAPED_SLASHES));
        $this->seeJsonContains(['print']);
    }
}
