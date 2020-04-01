<?php

namespace App\Http\Controllers;

use App\Follower;
use App\Quote;
use App\UserFeed;
use App\PushMessage;
use Illuminate\Http\Request;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;
use FCM;

class ScheduledJobsController extends Controller {

    public function feedQuotes(Request $request) {
        $quotes = Quote::where('active', 1)
                ->where('is_feeded', 0)
                ->get();
        foreach ($quotes as $quote) {
            $followers = Follower::where('user_id', $quote->user_id)->get();

            foreach ($followers as $follower) {
                $userFeed = new UserFeed();
                $userFeed->user_id = $follower->follower_id;
                $userFeed->quote_id = $quote->id;
                $userFeed->quote_user_id = $quote->user_id;
                $userFeed->save();
            }

            $quote->is_feeded = 1;
            $quote->save();
        }
    }

    public function sendPushmessage(Request $request) {
        $messages = PushMessage::where('is_sent', 0)
                ->where('num_attempts', '<', "3")
                ->orderBy('id', 'asc')
                ->get();

        foreach ($messages as $message) {
            if ($message->target_type == config('api.target_type_single')) {

                $optionBuilder = new OptionsBuilder();
                //$optionBuilder->setTimeToLive(60 * 20);
                $optionBuilder->setPriority("high");

                /* $notificationBuilder = new PayloadNotificationBuilder('my title');
                  $notificationBuilder->setBody('Hello world')
                  ->setSound('default'); */

                $dataBuilder = new PayloadDataBuilder();

                if (!empty($message->title)) {
                    $dataBuilder->addData(['title' => $message->title]);
                }

                if (!empty($message->message)) {
                    $dataBuilder->addData(['message' => $message->message]);
                }

                if (!empty($message->image)) {
                    $dataBuilder->addData(['imageUrl' => $message->image]);
                }

                if ($message->push_type == config('api.push_type_app_upgrade')) {
                    $data = json_decode($message->data);
                    $dataBuilder->addData(['pushType' => config('api.push_type_app_upgrade')]);
                    $dataBuilder->addData(['appLiveVersionCode' => config('api.app_live_version_code')]);
                    $dataBuilder->addData(['autoUpgrade' => true]);
                } else if ($message->push_type == config('api.push_type_quote')) {
                    $data = json_decode($message->data);
                    $dataBuilder->addData(['pushType' => config('api.push_type_quote')]);
                    $dataBuilder->addData(['quoteId' => $data->quoteId]);
                } else if ($message->push_type == config('api.push_type_author')) {
                    $data = json_decode($message->data);
                    $dataBuilder->addData(['pushType' => config('api.push_type_author')]);
                    $dataBuilder->addData(['authorId' => $data->authorId]);
                } else {
                    if (!empty($message->redirect_activity)) {
                        $dataBuilder->addData(['targetActivity' => $message->redirect_activity]);
                    }
                }

                $option = $optionBuilder->build();
                //$notification = $notificationBuilder->build();
                $notification = null;
                $data = $dataBuilder->build();

                $token = $message->target_id;

                $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

                if ($downstreamResponse->numberSuccess() >= 1) {
                    $message->is_sent = 1;
                    $message->date_sent = date('Y-m-d H:i:s', strtotime("now"));
                }
                $message->num_attempts = $message->num_attempts + 1;
                $message->date_last_attempt = date('Y-m-d H:i:s', strtotime("now"));
                // $message->fcm_response = $downstreamResponse;
                $message->save();

                dd($downstreamResponse);

                /*
                  $downstreamResponse->numberFailure();

                  $downstreamResponse->numberModification();

                  //return Array - you must remove all this tokens in your database
                  $downstreamResponse->tokensToDelete();

                  //return Array (key : oldToken, value : new token - you must change the token in your database )
                  $downstreamResponse->tokensToModify();

                  //return Array - you should try to resend the message to the tokens in the array
                  $downstreamResponse->tokensToRetry(); */
            } else if ($message->target_type == config('api.target_type_topic')) {

                /* $notificationBuilder = new PayloadNotificationBuilder('my title');
                  $notificationBuilder->setBody('Hello world')
                  ->setSound('default');

                  $notification = $notificationBuilder->build(); */

                $optionBuilder = new OptionsBuilder();
                //$optionBuilder->setTimeToLive(60 * 20);
                $optionBuilder->setPriority("high");

                $dataBuilder = new PayloadDataBuilder();

                if (!empty($message->title)) {
                    $dataBuilder->addData(['title' => $message->title]);
                }

                if (!empty($message->message)) {
                    $dataBuilder->addData(['message' => $message->message]);
                }

                if (!empty($message->image)) {
                    $dataBuilder->addData(['imageUrl' => $message->image]);
                }

                if ($message->push_type == config('api.push_type_app_upgrade')) {
                    $data = json_decode($message->data);
                    $dataBuilder->addData(['pushType' => config('api.push_type_app_upgrade')]);
                    $dataBuilder->addData(['appLiveVersionCode' => config('api.app_live_version_code')]);
                    $dataBuilder->addData(['autoUpgrade' => true]);
                } else if ($message->push_type == config('api.push_type_quote')) {
                    $data = json_decode($message->data);
                    $dataBuilder->addData(['pushType' => config('api.push_type_quote')]);
                    $dataBuilder->addData(['quoteId' => $data->quoteId]);
                } else {
                    if (!empty($message->redirect_activity)) {
                        $dataBuilder->addData(['targetActivity' => $message->redirect_activity]);
                    }
                }
                $option = $optionBuilder->build();
                //$notification = $notificationBuilder->build();
                $notification = null;
                $data = $dataBuilder->build();

                $topic = new Topics();
                $topic->topic($message->target_id);

                $topicResponse = FCM::sendToTopic($topic, $option, $notification, $data);

                if ($topicResponse->isSuccess()) {
                    $message->is_sent = 1;
                    $message->date_sent = date('Y-m-d H:i:s', strtotime("now"));
                }

                $message->num_attempts = 3;
                $message->date_last_attempt = date('Y-m-d H:i:s', strtotime("now"));
                //$message->fcm_response = $downstreamResponse;
                $message->save();

                /* $topicResponse->shouldRetry();
                  $topicResponse->error(); */
            }
        }
    }

}
