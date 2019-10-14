<?php

namespace RootInc\LaravelHerokuAppCreator;

use Illuminate\Http\Request;

use Symfony\Component\Console\Output\BufferedOutput;

use AWS;

class AppCreator
{
    //the url to heroku
    public $heroku_url = "https://dashboard.heroku.com/new";

    private $iamClient; //aws iam client
    private $s3Client; //aws s3 client

    /**
     * Creates a AppCreator
     *
     * @return AppCreator
     */
    public function __construct()
    {
        $this->iamClient = AWS::createClient('iam');
        $this->s3Client = AWS::createClient('s3');
    }

    /**
     * Handle an incoming request to redirect to heroku
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function route(Request $request)
    {
        $heroku_app_name = $request->input('app_name');
        $heroku_app_url = $request->input('app_url');

        if (!isset($heroku_app_name) || !isset($heroku_app_url))
        {
            return response('Please supply `app_name` and `app_url`.', 400);
        }

        $url = $this->createUrl($heroku_app_name, $heroku_app_url);
        
        return redirect($url);
    }

    /**
     * Creates the redirect url string to Heroku to create an app
     *
     * @param String $heroku_app_url
     * @param String $heroku_app_name
     * @return String
     */
    public function createUrl($heroku_app_url, $heroku_app_name)
    {
        $url = $this->heroku_url . '?template=' . urlencode(config('app_creator.template_url'));

        $heroku_app_key = $this->artisanKeyGenerate();

        list($heroku_aws_key, $heroku_aws_secret) = $this->createAndSetAWS($heroku_app_name);

        $this->urlConcatenate($url, $heroku_app_name, $heroku_app_url, $heroku_app_key, $heroku_aws_key, $awsSecret);

        return $url;
    }

    /**
     * Modifies the $url variable by appending additional url params
     *
     * @param String &$url
     * @param String $heroku_app_name
     * @param String $heroku_app_url
     * @param String $heroku_app_key
     * @param String $heroku_aws_key
     * @param String $heroku_aws_secret
     * @return void
     */
    public function urlConcatenate(&$url, $heroku_app_name, $heroku_app_url, $heroku_app_key, $heroku_aws_key, $heroku_aws_secret)
    {
        $url .= '&env[APP_KEY]=' . urlencode($heroku_app_key);
        $url .= '&env[APP_URL]=' . urlencode($heroku_app_url);
        $url .= '&env[AWS_KEY]=' . urlencode($heroku_aws_key);
        $url .= '&env[AWS_SECRET]=' . urlencode($heroku_aws_secret);
        $url .= '&env[AWS_UPLOAD_FOLDER]=' . urlencode($heroku_app_name);
    }

    /**
     * Generates an app key
     *
     * @return String
     */
    public function artisanKeyGenerate()
    {
        $output = new BufferedOutput;

        \Artisan::call('key:generate', [
            '--show' => true,
        ], $output);

        return str_replace(PHP_EOL, '', $output->fetch());
    }

    /**
     * Creates and Sets Up all the AWS related things.
     * Returns the access key and secret access key
     *
     * @param String $heroku_app_name
     * @return Array
     */
    public function createAndSetAWS($heroku_app_name)
    {
        list($heroku_aws_key, $heroku_aws_secret) = $this->createIamUser($heroku_app_name);
        $this->addIamUserToGroup($heroku_app_name);
        $this->createS3Folder($heroku_app_name);

        return [
            $heroku_aws_key,
            $heroku_aws_secret
        ];
    }

    /**
     * Creates an IAM user with the username of the heroku app name.
     * Returns the access key and secret access key
     *
     * @param String $heroku_app_name
     * @return Array
     */
    public function createIamUser($heroku_app_name)
    {
        $this->iamClient->createUser([
            'UserName' => $heroku_app_name,
        ]);

        $results = $this->iamClient->createAccessKey([
            'UserName' => $heroku_app_name,
        ]);

        $heroku_aws_key = $results['AccessKey']['AccessKeyId'];
        $heroku_aws_secret = $results['AccessKey']['SecretAccessKey'];

        return [
            $heroku_aws_key,
            $heroku_aws_secret
        ];
    }

    /**
     * Adds the IAM user to the specified configured group based on the heroku app name
     *
     * @param String $heroku_app_name
     * @return void
     */
    public function addIamUserToGroup($heroku_app_name)
    {
        $this->iamClient->addUserToGroup([
            'GroupName' => config('app_creator.iam_group'),
            'UserName' => $heroku_app_name,
        ]);
    }

    /**
     * Creates an s3 folder in the specified configured bucket based on the heroku app name
     *
     * @param String $heroku_app_name
     * @return void
     */
    public function createS3Folder($heroku_app_name)
    {
        $this->s3Client->putObject([
            'ACL' => 'private',
            'Bucket' => config('app_creator.aws_bucket')
            'Key' => $heroku_app_name . '/'
        ]);
    }
}