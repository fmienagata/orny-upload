<?php

declare(strict_types=1);

namespace UploadBundle\Tests;

/**
 * Inherited Methods.
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function authenticate(array $credential = []): string
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->haveHttpHeader('Accept', 'application/json');
        $this->haveHttpHeader('cc', $_ENV['MONGODB_FIXTURES_URI']);
        $this->haveHttpHeader('x-api-key', $_ENV['API_KEY']);
        if (empty($credential)) {
            $this->sendAsJson('post', 'login', ['username' => 'John', 'password' => 'Docaposte2*']);
        } else {
            $this->sendAsJson('post', 'login', [
                'username' => $credential['username'],
                'password' => $credential['password']
            ]);
        }
        $response = $this->grabResponse();
        $responseArray = json_decode($response, true);

        return $responseArray['token'];
    }

    public function getCurrentUser(string $token): string
    {
        $this->amBearerAuthenticated($token);
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->haveHttpHeader('cc', $_ENV['MONGODB_FIXTURES_URI']);
        $this->haveHttpHeader('x-api-key', $_ENV['API_KEY']);
        $this->sendGet('users/current/user');
        $response = $this->grabResponse();
        $responseArray = json_decode($response, true);

        return $responseArray['id'];
    }
}
