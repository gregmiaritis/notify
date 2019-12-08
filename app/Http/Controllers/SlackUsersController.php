<?php

namespace App\Http\Controllers;

use SlackHelper;
use App\SlackUser;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class SlackUsersController extends Controller
{
    private $slack_api_link;
    private $slack_api_token;
    private $slack_bot_user_token;
    private $slack_api_token_type;
    private $slack_post_message_endpoint;
    private $slack_users_endpoint;

    public function __construct()
    {
        $this->slack_api_link = config('services.slack.link');
        $this->slack_api_token = config('services.slack.token');
        $this->slack_api_token_type = config('services.slack.token_type');
        $this->slack_users_endpoint = config('services.slack.users_endpoint');
        $this->slack_bot_user_token = config('services.slack.bot_user_token');
        $this->slack_post_message_endpoint = config('services.slack.post_message_endpoint');
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

        return response()->json('Imported', Response::HTTP_OK);
    }

    public function sendSlackMessage()
    {
        $slack_user = SlackUser::find(4); //make it dynamic

        if (!$slack_user->private_channel_id) {
            $slack_user = SlackHelper::getPrivateChannelId($slack_user->id);
        }

        $client = new Client;
        $client->post(
            $this->slack_api_link.
            $this->slack_post_message_endpoint.
            SlackHelper::sendSlackMessage($this->slack_bot_user_token, $slack_user)
        );

        return response()->json('Message has been sent', Response::HTTP_OK);
    }
}
