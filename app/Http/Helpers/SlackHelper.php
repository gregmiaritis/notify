<?php

namespace App\Http\Helpers;

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

        return $slack_user->private_channel_id;
    }
}
