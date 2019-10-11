<?php

namespace RootInc\AppCreator;

use Illuminate\Http\Request;

use Symfony\Component\Console\Output\BufferedOutput;

use AWS;

class AppCreator extends Controller
{
    private $iamClient;
    private $s3Client;

    public function __construct()
    {
        $this->iamClient = AWS::createClient('iam');
        $this->s3Client = AWS::createClient('s3');
    }

    public function route(Request $request)
    {
        $clientName = $request->input('clientName');
        $subdomain = strtolower($request->input('subdomain'));
        $orgId = $request->input('orgId');

        if (!isset($clientName) || !isset($subdomain))
        {
            return response('Please supply `clientName` and `subdomain`.', 400);
        }

        $url = $this->createUrl($clientName, $subdomain, $orgId);
        
        return redirect($url);
    }

    public function createUrl($clientName, $subdomain, $orgId)
    {
        $url = env('HEROKU_URL') . '?template=' . urlencode(env('TEMPLATE_URL'));

        $appName = "continuum-" . implode('-', explode(' ',strtolower($clientName)));

        $appKey = $this->artisanKeyGenerate();

        $appUrl = "https://" . $subdomain . "." . env('CONTINUUM_DOMAIN');

        list($awsKey, $awsSecret) = $this->createAndSetAWS($appName);

        $this->urlConcatenate($url, $clientName, $orgId, $appName, $appUrl, $appKey, $awsKey, $awsSecret);

        return $url;
    }

    public function urlConcatenate(&$url, $clientName, $orgId, $appName, $appUrl, $appKey, $awsKey, $awsSecret)
    {
        $url .= '&env[APP_KEY]=' . urlencode($appKey);
        $url .= '&env[APP_URL]=' . urlencode($appUrl);
        $url .= '&env[AWS_KEY]=' . urlencode($awsKey);
        $url .= '&env[AWS_SECRET]=' . urlencode($awsSecret);
        $url .= '&env[AWS_UPLOAD_FOLDER]=' . urlencode($appName);
        $url .= '&env[SSH_KEY]=' . urlencode(env('SSH_KEY'));
        $url .= '&env[MAIL_PASSWORD]=' . urlencode(env('MAIL_PASSWORD'));
        $url .= '&env[MAIL_ENCRYPTION]=null';
        $url .= '&env[ORG_ID]=' . urlencode($orgId);
        $url .= '&env[SSO_NAME]=' . urlencode("Continuum " . $clientName);
    }

    public function artisanKeyGenerate()
    {
        $output = new BufferedOutput;

        \Artisan::call('key:generate', [
            '--show' => true,
        ], $output);

        return str_replace(PHP_EOL, '', $output->fetch());
    }

    public function createAndSetAWS($appName)
    {
        list($awsKey, $awsSecret) = $this->createIamUser($appName);
        $this->addIamUserToGroup($appName);
        $this->createS3Folder($appName);

        return [
            $awsKey,
            $awsSecret
        ];
    }

    public function createIamUser($appName)
    {
        $this->iamClient->createUser([
            'UserName' => $appName,
        ]);

        $results = $this->iamClient->createAccessKey([
            'UserName' => $appName,
        ]);

        $awsKey = $results['AccessKey']['AccessKeyId'];
        $awsSecret = $results['AccessKey']['SecretAccessKey'];

        return [
            $awsKey,
            $awsSecret
        ];
    }

    public function addIamUserToGroup($appName)
    {
        $this->iamClient->addUserToGroup([
            'GroupName' => env('IAM_GROUP'),
            'UserName' => $appName,
        ]);
    }

    public function createS3Folder($appName)
    {
        $this->s3Client->putObject([
            'ACL' => 'private',
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $appName . '/'
        ]);
    }
}