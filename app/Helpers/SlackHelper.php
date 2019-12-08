<?php

namespace App\Helpers;

use App\SlackUser;
use GuzzleHttp\Client;

class SlackHelper
{
    public static function getPrivateChannelId($slack_user_id)
    {
        $slack_api_link = config('services.slack.link');
        $slack_api_token = config('services.slack.token');
        $slack_im_open_endpoint = config('services.slack.im_open_endpoint');
        
        $slack_user = SlackUser::find($slack_user_id);

        $client = new Client;
        $request = $client->post(
            $slack_api_link.
            $slack_im_open_endpoint.
            '?token='.$slack_api_token.
            '&user='.$slack_user->slack_id
        );
        
        $request = json_decode($request->getBody()->getContents());

        $slack_user->private_channel_id = $request->channel->id;
        $slack_user->update();

        return $slack_user;
    }

    public static function sendSlackMessage($token, $slack_user)
    {
        $attachments = [
            [
                "color" => "#2eb886",
                "author_name" => "New pull request has been raised.",
                "fields" => [
                    [
                        "title" => "Author",
                        "value" => "Greg",
                        "short" => true
                    ],
                    [
                        "title" => "Repo",
                        "value" => "High",
                        "short" => true
                    ]
                ],
                "actions" => [
                    [
                        "text" => "View PR  💻",
                        "type" => "button",
                        "url" => "https://google.com"
                    ]
                ]
            ]
        ];

        $data = http_build_query([
            "token" => $token,
            "channel" => $slack_user->private_channel_id,
            "attachments" => json_encode($attachments),
        ]);

        return '?'.$data;
    }
}
