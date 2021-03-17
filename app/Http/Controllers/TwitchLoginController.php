<?php

namespace App\Http\Controllers;


use App\TwitchClient;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Minicli\Curly\Client;

class TwitchLoginController extends Controller
{
    private $my_user_id;

    public function __construct()
    {

        $this->my_user_id = 191013885;
    }

    public function mainNovo(Request $request)
    {
        $client_id     = env('TWITCH_CLIENT_ID');
        $client_secret = env('TWITCH_CLIENT_SECRET');
        $redirect_uri  = env('TWITCH_CALLBACK_URL');

        $twitch_client = new TwitchClient($client_id, $client_secret, $redirect_uri);

        $state = $request->query('state');

        if ($state === null) {
            $state    = md5(time());
            $auth_url = $twitch_client->getAuthURL($state);

            return redirect($auth_url);
        }

        $code     = $request->query('code');
        $response = $twitch_client->getUserToken($code);

        if ($response['code'] !== 200) {
            echo "ERROR.";
            return print_r($response);
        }

        $token_response = json_decode($response['body'], 1);
        $access_token   = $token_response['access_token'];

        $user_info = $twitch_client->getCurrentUser($access_token);
        $user_info = $user_info['data'][0] ?? 0;

        if ($user_info) {

            $twitch_client->follow(new Client(), $user_info['id'], $this->my_user_id, $access_token);

            $user = User::firstOrNew([
                'twitch_id' => $user_info['id'],
            ]);

            if ($user->username) {
                $user->profile_image_url = $user_info['profile_image_url'];
                $user->save();
                Auth::login($user);
                return redirect()->route('index');
            }

            $user->username    = $user_info['login'];
            $user->twitch_id   = $user_info['id'];
            $user->email       = $user_info['email'];
            $user->password    = md5(time());
            $user->oauth_token = $access_token;
            $user->save();

            Auth::login($user);
        }
        return redirect()->route('index');
    }

}
