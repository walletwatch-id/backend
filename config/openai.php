<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Assistant
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI Assistant. This will be used to interact
    | with the OpenAI Assistant API - you can find your Assistant ID on your
    | OpenAI dashboard, at https://openai.com.
    */

    'assistant' => env('OPENAI_ASSISTANT'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 60 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 60),
];
