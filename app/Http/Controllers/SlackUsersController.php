<?php

namespace App\Http\Controllers;

use App\SlackUser;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SlackUsersController extends Controller
{
    private $slack_api_link;
    private $slack_api_token;
    private $slack_api_token_type;
    private $slack_users_endpoint;

    public function __construct()
    {
        $this->slack_api_link = config('services.slack.link');
        $this->slack_api_token = config('services.slack.token');
        $this->slack_api_token_type = config('services.slack.token_type');
        $this->slack_users_endpoint = config('services.slack.users_endpoint');
    }

    public function importSlackUsers()
    {
        $client = new Client;

        $request = $client->get($this->slack_api_link.$this->slack_users_endpoint, [
            'headers' => [
                'Authorization' => $this->slack_api_token_type.' '.$this->slack_api_token,
            ]
        ]);

        $slack_users = json_decode($request->getBody()->getContents());

        foreach ($slack_users->members as $slack_user) {
            if (!$slack_user->is_bot && $slack_user->name != 'slackbot') {
                SlackUser::firstOrCreate(
                    [
                        'slack_id' => $slack_user->id
                    ],
                    [
                        'name' => $slack_user->name,
                        'email' => $slack_user->profile->email,
                        'title' => $slack_user->profile->title,
                        'real_name' => $slack_user->real_name
                    ]
                );
            }
        }

        return new JsonResponse(['message' => 'Message has been sent'], Response::HTTP_OK);
    }

}
