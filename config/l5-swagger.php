<?php

return [

    /*
    |--------------------------------------------------------------------------
    | L5 Swagger Documentation Settings
    |--------------------------------------------------------------------------
    |
    | This file contains all settings for L5 Swagger integration.
    |
    */

    'default' => 'default',

    'documentations' => [

        'default' => [
            'api' => [
                'title' => env('APP_NAME', 'API Documentation'),
            ],

            'routes' => [
                'api' => 'api/documentation', // URL pour accéder à Swagger UI
            ],

            'paths' => [
                'use_absolute_path' => true,
                'swagger_ui_assets_path' => base_path('vendor/swagger-api/swagger-ui/dist'),
                'asset_base_path' => env('L5_SWAGGER_ASSET_BASE', '/vendor/swagger-ui'),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                'docs' => storage_path('api-docs'),
                'views' => resource_path('views/vendor/l5-swagger'),
                'assets' => public_path('vendor/swagger-ui'),
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
        ],

        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => resource_path('views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', '/api/v1'),
            'excludes' => [],
        ],

        'swagger' => [
            'swagger' => '2.0',
            'info' => [
                'title' => env('APP_NAME', 'API Documentation'),
                'version' => '1.0.0',
            ],
            'host' => env('L5_SWAGGER_CONST_HOST', 'localhost'),
            'basePath' => '/',
            'schemes' => [env('L5_SWAGGER_CONST_SCHEME', 'http')],
        ],

        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'localhost'),
            'L5_SWAGGER_CONST_SCHEME' => env('L5_SWAGGER_CONST_SCHEME', 'http'),
            'L5_SWAGGER_ASSET_BASE' => env('L5_SWAGGER_ASSET_BASE', '/vendor/swagger-ui'),
        ],

        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        'proxy' => false,

        'additional_config_url' => null,

        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

        'validator_url' => null,

        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],
            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),
            ],
        ],

        'securityDefinitions' => [
            'securitySchemes' => [],
            'security' => [],
        ],

        'scanOptions' => [
            'paths' => [
                base_path('app/Http/Controllers'),
                base_path('app/Swagger/Schemas'),
            ],
            'exclude' => [],
            'open_api_spec_version' => \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION,
        ],
    ],
];
