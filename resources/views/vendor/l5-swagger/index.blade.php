<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('l5-swagger.documentations.' . $documentation . '.api.title') }}</title>

    {{-- ✅ Assets Swagger sécurisés --}}
    <link rel="stylesheet" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>

    {{-- ✅ Styles de base --}}
    <style>
        html { box-sizing: border-box; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>

    {{-- ✅ Dark mode si activé --}}
    @if(config('l5-swagger.defaults.ui.display.dark_mode'))
    <style>
        body#dark-mode { background: #1b1b1b; color: #e7e7e7; }
        #swagger-ui .opblock { background-color: #2c2c2c; }
        #swagger-ui .opblock-summary { color: #e7e7e7; }
    </style>
    @endif
</head>

<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
    <div id="swagger-ui"></div>

    {{-- ✅ Scripts Swagger sécurisés --}}
    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>

    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                dom_id: '#swagger-ui',
                url: "{!! $urlToDocs !!}",
                layout: "StandaloneLayout",
                presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
                plugins: [SwaggerUIBundle.plugins.DownloadUrl],
                deepLinking: true,
                docExpansion: "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
                filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
                persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
