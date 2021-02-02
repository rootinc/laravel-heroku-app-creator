<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Template URL
    |--------------------------------------------------------------------------
    |
    | This value is equal to the url in github pointed to the root where
    | app.json is located
    |
    */
    'template_url' => env('TEMPLATE_URL'),

    /*
    |--------------------------------------------------------------------------
    | IAM Group
    |--------------------------------------------------------------------------
    |
    | This value is equal to the group name that programmatic users created
    | should be assigned to
    |
    */
    'iam_group' => env('IAM_GROUP'),

    /*
    |--------------------------------------------------------------------------
    | AWS Bucket
    |--------------------------------------------------------------------------
    |
    | The bucket name of app we are creating the S3 folder in
    |
    */
    'aws_bucket' => env('AWS_BUCKET'),

    /*
    |--------------------------------------------------------------------------
    | AWS Default Region
    |--------------------------------------------------------------------------
    |
    | The default region of the app we are creating the S3 folder in
    |
    */
    'aws_default_region' => env('AWS_DEFAULT_REGION'),
];