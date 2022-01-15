<?php
return [
    '*' => [
        'pipeline' => 'manifest|passthrough',
        'manifestPath' => 'rev-manifest.json',
        'assetsBasePath' => '/assets/dist/',
        'assetUrlPrefix' => '@baseUrl/assets/dist/'
    ],
];
