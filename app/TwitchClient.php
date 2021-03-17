<?php

namespace App;

use Minicli\Curly\Client;

class TwitchClient
{
    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;
    protected $curly;

    static $login_url = 'https://id.twitch.tv/oauth2/authorize';
    static $token_url = 'https://id.twitch.tv/oauth2/token';
    static $validate_url = 'https://api.twitch.tv/helix/users';
    static $users_follows_url = 'https://api.twitch.tv/helix/users/follows';

    public function __construct(string $client_id, string $client_secret, string $redirect_uri)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;

        $this->curly = new Client();
    }

    public function getAuthURL(string $state)
    {
        return sprintf(
            '%s?response_type=code&client_id=%s&redirect_uri=%s&state=%s&scope=%s',
            self::$login_url,
            $this->client_id,
            $this->redirect_uri,
            $state,
            "channel:read:subscriptions+viewing_activity_read+user:read:email+user:edit:follows+openid"
        );
    }

    public function getUserToken($code)
    {
        return $this->curly->post(sprintf(
            '%s?code=%s&client_id=%s&client_secret=%s&grant_type=authorization_code&redirect_uri=%s',
            self::$token_url,
            $code,
            $this->client_id,
            $this->client_secret,
            $this->redirect_uri
        ), [], ['Accept:', 'application/json']);
    }

    public function getCurrentUser($access_token)
    {
        $response = $this->curly->get(
            self::$validate_url,
            $this->getHeaders($this->client_id, $access_token)
        );

        if ($response['code'] == 200) {
            return json_decode($response['body'], 1);
        }

        return null;
    }

    public function follow(Client $client, $from_id, $to_id, $access_token)
    {
        $response = $client->post(
            self::$users_follows_url,
            [
                'from_id' => $from_id,
                'to_id'   => $to_id
            ],
            [
                "Authorization: Bearer $access_token",
                "Client-Id: " . $this->client_id,
                'Content-Type: application/json',
            ]
        );
        if ($response['code'] === 204) {
            print_r('ok');
        } else {
            print_r($response);
        }

    }

    public function getHeaders($client_id, $access_token, $type = 'Bearer'): array
    {
        return [
            "Client-ID: $client_id",
            "Authorization: $type $access_token",
        ];
    }
}
