<?php

namespace App\Http\Controllers;

use App\SlackUser;
use App\Helpers\SlackHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WebhooksController extends Controller
{
    const OPENED_PR = 'opened';
    const OPEN_PR_GITHUB = 'open';
    
    public function gitlab(Request $request)
    {
        $slack_user = SlackUser::find(4); //make it dynamic
        
        $author = SlackHelper::prepareData($request->user);
        $pull_request = SlackHelper::prepareData($request->object_attributes);

        if ($pull_request->state === WebhooksController::OPENED_PR) {
            $pull_request_data = SlackHelper::gitlabData($pull_request, $author);
            SlackHelper::sendSlackMessage($slack_user, $pull_request_data);
        }
        
        return new JsonResponse(['message' => 'Message has been sent'], Response::HTTP_OK);
    }
    
    public function bitbucket(Request $request)
    {
        $slack_user = SlackUser::find(4); //make it dynamic
        
        $pull_request_data = SlackHelper::prepareData($request->pull_request);
        
        SlackHelper::sendSlackMessage($slack_user, $pull_request_data);
        
        return new JsonResponse(['message' => 'Message has been sent'], Response::HTTP_OK);
    }
    
    public function github(Request $request)
    {
        $pull_request = json_decode($request->payload);

        $slack_user = SlackUser::find(4); //make it dynamic

        if ($pull_request->pull_request->state === WebhooksController::OPEN_PR_GITHUB) {
            $pull_request_data = SlackHelper::githubData($pull_request->pull_request);
            SlackHelper::sendSlackMessage($slack_user, $pull_request_data);
        }

        return new JsonResponse(['message' => 'Message has been sent'], Response::HTTP_OK);
    }
}
