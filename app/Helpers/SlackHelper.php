<?php

namespace App\Helpers;

use App\SlackUser;
use GuzzleHttp\Client;

class SlackHelper
{
    public static function prepareData($data)
    {
        $data = json_decode(json_encode($data));

        return $data;
    }

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

    public static function sendSlackMessage($slack_user, $pull_request)
    {
        $slack_api_link = config('services.slack.link');
        $slack_bot_user_token = config('services.slack.bot_user_token');
        $slack_post_message_endpoint = config('services.slack.post_message_endpoint');

        if (!$slack_user->private_channel_id) {
            $slack_user = SlackHelper::getPrivateChannelId($slack_user->id);
        }

        $client = new Client;
        $client->post(
            $slack_api_link.
            $slack_post_message_endpoint.
            SlackHelper::composeSlackMessage($slack_bot_user_token, $slack_user, $pull_request)
        );
    }

    public static function composeSlackMessage($token, $slack_user, $pull_request)
    {
        $attachments = [
            [
                "color" => "#2eb886",
                "author_name" => "New pull request has been raised.",
                "fields" => [
                    [
                        "title" => "Author",
                        "value" => $pull_request['author'],
                        "short" => true
                    ],
                    [
                        "title" => "Repository",
                        "value" => $pull_request['repo'],
                        "short" => true
                    ]
                ],
                "actions" => [
                    [
                        "text" => "View PR  💻",
                        "type" => "button",
                        "url" => $pull_request['url']
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

    public static function gitlabData($pull_request, $author)
    {
        $data = [];

        $data['author'] = $author->name;
        $data['repo'] = $pull_request->source->name;
        $data['url'] = $pull_request->url;

        return $data;
    }

    public static function bitbucketData($pull_request)
    {
        $data = [];

        $data['author'] = $pull_request->author->display_name;
        $data['repo'] = $pull_request->source->repository->name;
        $data['url'] = $pull_request->links->html->href;

        return $data;
    }
}
