# Laravel Heroku App Creator

Creates an App on Heroku using the Heroku Quick Deployer, as well as setups simple AWS credentials, and generates an app key.

## Normal Installation

1. `composer require rootinc/laravel-heroku-app-creator`
2. run `php artisan vendor:publish --provider="RootInc\LaravelHerokuAppCreator\AppCreatorServiceProvider"` to install config file to `config/app_creator.php`
3. In our routes folder (most likely `web.php`), add
```php
Route::get('/create-app', '\RootInc\LaravelHerokuAppCreator\AppCreator@route');
```

4. In our `.env` add `AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_REGION and AWS_BUCKET`.  The access key and secret key is used to create S3 folders and IAM users, so be sure this programmatic user has the correct permissions.  The `AWS_BUCKET` is where all the apps folders will get generated.
5. Also in our `.env` add `IAM_GROUP`, and `TEMPLATE_URL`.  `IAM_GROUP` is the name of the group you want to assign the newly created AWS user for each Heroku app.  `TEMPLATE_URL` is the url used with Heroku to create a new app.  Make sure this repo has an app.json file.  Ex: `TEMPLATE_URL=https://github.com/rootinc/laravel-heroku-app-creator/tree/master`.

## Routing

`Route::get('/create-app', '\RootInc\LaravelHerokuAppCreator\AppCreator@route');` First parameter can be wherever you want to route the app creator route.  Change as you would like.

## Contributing

Thank you for considering contributing to the Laravel Heroku App Creator! To encourage active collaboration, we encourage pull requests, not just issues.

If you file an issue, the issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a issue is to make it easy for yourself - and others - to replicate the bug and develop a fix.

## License

The Laravel Heroku App Creator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
