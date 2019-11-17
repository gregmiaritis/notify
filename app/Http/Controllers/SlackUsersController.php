<?php

namespace App\Http\Controllers;

use App\SlackUser;
use Symfony\Component\HttpFoundation\Response;

class SlackUsersController extends Controller
{
    private $slack_api_link, $slack_api_token, $slack_api_token_type;

    public function __construct()
    {
        $this->slack_api_link = config('services.slack.link');
        $this->slack_api_token = config('services.slack.token');
        $this->slack_api_token_type = config('services.slack.token_type');
    }

    public function importSlackUsers()
    {
        $client = new \GuzzleHttp\Client();

        $request = $client->get($this->slack_api_link, [
            'headers' => [
                'Authorization' => $this->slack_api_token_type.' '.$this->slack_api_token,
            ]
        ]);
        
        $slack_users = json_decode($request->getBody()->getContents());
        
        foreach ($slack_users->members as $slack_user) {
            if(!$slack_user->is_bot && $slack_user->name != 'slackbot') {
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

        return response()->json('Imported', Response::HTTP_OK);
    }
}
