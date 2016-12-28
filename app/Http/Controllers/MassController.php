<?php

namespace Guo\Wechat\Http\Controllers;



use Illuminate\Http\Request;
use Guo\Wechat\Model\MassLog;
use Guo\Wechat\Model\Media;
use Guo\Wechat\Model\UserSns;
use Validator;
use DB;
use Exception;
use WechatToken;
use EasyWeChat\Core\AccessToken;
use Illuminate\Support\Facades\Redis;

use EasyWeChat\Message\Text;
use EasyWeChat\Message\Material;

class MassController extends Controller
{
    public $wechat;
    public $month = null;
    public $openId = null;
    public $number = null;
    public $tagId = null;
    public $text = null;

    public function __construct(Request $request)
    {
//        $options = config("app.options");
//        $app = new Application($options);
        $wechat = app('wechat');
        $wechatToken = new WechatToken();
        $accessToken = new AccessToken(config('app.wechatAppid'), config('app.wechatSecret'), $wechatToken);
        $accessToken->prefix = config('app.redisKey.wechatToken');
        $wechat['access_token'] = $accessToken;
        $this->wechat = $wechat;

        switch ($request->select) {
//            case 'tag':  
//                $this->validate($request, [
//                    'tagId' => 'required',
//                    'text' => 'required'
//                ]);
//                $this->text = $request->text;
//                $this->tagId = $request->tagId;
//                $tag = $wechat->user_tag; // $user['user_tag']      
//                $this->number = $tag->usersOfTag($this->tagId)->toArray()['count'];
//                
//                break;
            case 'test':

                break;
            case 'users'://所有用户发送


                $this->number = $wechat->user->lists()->total;
                break;
        }

    }

    public function sendText(Request $request)
    {
        if ($request->select == 'test') {
            echo 'Please select test send to group';
            die;
        }
        $validator = Validator::make($request->all(), [
            'text' => 'required',

        ]);
        if ($validator->fails()) {
            return redirect('/massf')
                ->withErrors($validator)
                ->withInput();

        }
        die;
        $number = $this->number;
        $wechat = $this->wechat;
        $text = $request->text;
        $broadcast = $wechat->broadcast;

        $msgId = null;
        if ($request->select == 'tag') {

            if ($this->number < 1) {
                echo 'this tag people less than one';
                die;
            }
            $sendResult = $broadcast->sendText($text, $this->tagId);
            $msgId = $sendResult->msg_id;

        } else {
            $sendResult = $broadcast->sendText($text);
            $msgId = $sendResult->msg_id;

        }
        if ($request->select == 'users') {
            $receiver = '所有用户';
        } else {
            $receiver = '标签id' . $this->tagId;
        }

        if ($sendResult->errcode == 0) {
            $massLog = new MassLog();
            $massLog->msgId = $msgId;
            $massLog->receiver = $receiver;
            $massLog->way = '正式群发';
            $massLog->number = $number;
            $massLog->contents = $text;
            $massLog->result = '提交成功';
            $massLog->save();
            return redirect('massf')->with('mgssages', array(0 => '提交成功'));
        } else {
            $massLog = new MassLog();
            $massLog->msgId = $msgId;
            $massLog->receiver = $receiver;
            $massLog->way = '正式群发';
            $massLog->number = $number;
            $massLog->contents = $text;
            $massLog->result = '提交失败';
            $massLog->save();
            return redirect('massf')->with('mgssages', array(0 => '提交失败'));
        }
//        $broadcast->sendNews($mediaId, [$openId1, $openId2]);
//        $broadcast->sendVoice($mediaId, [$openId1, $openId2]);
//        $broadcast->sendImage($mediaId, [$openId1, $openId2]);
//        $broadcast->sendVideo($message, [$openId1, $openId2]);
//        $broadcast->sendCard($cardId, [$openId1, $openId2]);
    }

    public function sendPicture(Request $request)
    {

        if ($request->select == 'test') {
            echo 'Please select test send to group';
            die;
        }
        $validator = Validator::make($request->all(), [
            'media_id' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect('/massf')
                ->withErrors($validator)
                ->withInput();

        }

        $number = $this->number;
        $wechat = $this->wechat;
        $mediaId = $request->media_id;
        $broadcast = $wechat->broadcast;

        $msgId = null;
        if ($request->select == 'tag') {

            if ($this->number < 1) {
                echo 'this tag people less than one';
                die;
            }
            //$sendResult = $broadcast->sendImage($text, $this->tagId);
            //$msgId = $sendResult->msg_id;

        } else {
            $sendResult = $broadcast->sendImage($mediaId);
            $msgId = $sendResult->msg_id;

        }
        if ($request->select == 'users') {
            $receiver = '所有用户';
        } else {
            $receiver = '标签id' . $this->tagId;
        }

        if ($sendResult->errcode == 0) {
            $massLog = new MassLog();
            $massLog->msgId = $msgId;
            $massLog->receiver = $receiver;
            $massLog->way = '正式群发';
            $massLog->number = $number;
            $massLog->contents = '临时图片媒体ID：' . $mediaId;
            $massLog->result = '提交成功';
            $massLog->save();
            return redirect('massf')->with('mgssages', array(0 => '提交成功'));
        } else {
            $massLog = new MassLog();
            $massLog->msgId = $msgId;
            $massLog->receiver = $receiver;
            $massLog->way = '正式群发';
            $massLog->number = $number;
            $massLog->contents = '临时图片媒体ID：' . $mediaId;
            $massLog->result = '提交失败';
            $massLog->save();
            return redirect('massf')->with('mgssages', array(0 => '提交失败'));
        }
//        $broadcast->sendNews($mediaId, [$openId1, $openId2]);
//        $broadcast->sendVoice($mediaId, [$openId1, $openId2]);
//        $broadcast->sendImage($mediaId, [$openId1, $openId2]);
//        $broadcast->sendVideo($message, [$openId1, $openId2]);
//        $broadcast->sendCard($cardId, [$openId1, $openId2]);
    }

    public function sendPictureText(Request $request)
    {

        if ($request->select == 'test') {
            echo 'Please select test send to group';
            die;
        }
        $validator = Validator::make($request->all(), [
            'media_id' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect('/massf')
                ->withErrors($validator)
                ->withInput();

        }

        $number = $this->number;
        $wechat = $this->wechat;
        $mediaId = $request->media_id;
        $arr = explode("#", $mediaId);
        $mediaId = $arr[0];

        $result = Media::where('id', $mediaId)->first(['bundle_id']);
        if ($result->bundle_id !== 0) {
            $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MIXED_MULTI'), $result->bundle_id));
        } else {
            $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MIXED'), $mediaId));
        }
        $title = $arr[1];

        $broadcast = $wechat->broadcast;

        $msgId = null;
        if ($request->select == 'tag') {

            if ($this->number < 1) {
                echo 'this tag people less than one';
                die;
            }
            //$sendResult = $broadcast->sendImage($text, $this->tagId);
            //$msgId = $sendResult->msg_id;

        } else {
            $sendResult = $broadcast->sendNews($mediaId);
            $msgId = $sendResult->msg_id;

        }
        if ($request->select == 'users') {
            $receiver = '所有用户';
        } else {
            $receiver = '标签id' . $this->tagId;
        }

        if ($sendResult->errcode == 0) {
            $massLog = new MassLog();
            $massLog->msgId = $msgId;
            $massLog->receiver = $receiver;
            $massLog->way = '正式群发';
            $massLog->number = $number;
            $massLog->contents = $title;
            $massLog->result = '提交成功';
            $massLog->save();
            return redirect('massf')->with('mgssages', array(0 => '提交成功'));
        } else {
            $massLog = new MassLog();
            $massLog->msgId = $msgId;
            $massLog->receiver = $receiver;
            $massLog->way = '正式群发';
            $massLog->number = $number;
            $massLog->contents = $title;
            $massLog->result = '提交失败';
            $massLog->save();
            return redirect('massf')->with('mgssages', array(0 => '提交失败'));
        }
//        $broadcast->sendNews($mediaId, [$openId1, $openId2]);
//        $broadcast->sendVoice($mediaId, [$openId1, $openId2]);
//        $broadcast->sendImage($mediaId, [$openId1, $openId2]);
//        $broadcast->sendVideo($message, [$openId1, $openId2]);
//        $broadcast->sendCard($cardId, [$openId1, $openId2]);
    }

    public function preSendText(Request $request)
    {
        if (!($request->select == 'test')) {
            echo 'Please select send to group';
            die;
        }

        $validator = Validator::make($request->all(), [
            'userId' => 'required',
            'text' => 'required',

        ]);

        $wechat = $this->wechat;
        $userId = $request->userId;
        $text = $request->text;

        if ($validator->fails()) {
            return redirect('/masstest')
                ->withErrors($validator)
                ->withInput();

        } else {
            try {
                $user = UserSns::where('user_id', $userId)->where('status', '0')->first(['openid']);
                $openId = $user->openid;
            } catch (Exception $e) {
                return redirect('/massf')
                    ->withErrors(array(0 => 'userId不正确/用户没有关注'))
                    ->withInput();
            }

            $broadcast = $wechat->broadcast; //var_dump($openId);die;
            // TEXT别名方式    
            $sendResult = $broadcast->previewText($text, $openId); //var_dump($sendResult);die;
            $msgId = '测试无消息id';

            if ($sendResult->errcode == 0) {
                $massLog = new MassLog();
                $massLog->number = 1;
                $massLog->msgId = $msgId;
                $massLog->receiver = 'userID' . $userId;
                $massLog->way = '测试群发';
                $massLog->contents = $text;
                $massLog->result = '提交成功';
                $massLog->save();
                return redirect('masstest')->with('mgssages', array(0 => '提交成功'));
            } else {
                $massLog = new MassLog();
                $massLog->number = 1;
                $massLog->msgId = $msgId;
                $massLog->receiver = 'userID' . $userId;
                $massLog->way = '测试群发';
                $massLog->contents = $text;
                $massLog->result = '提交失败';
                $massLog->save();
                return redirect('masstest')->with('mgssages', array(0 => '提交失败'));
            }
        }


    }

    public function preSendPicture(Request $request)
    {
        if (!($request->select == 'test')) {
            echo 'Please select send to group';
            die;
        }

        $validator = Validator::make($request->all(), [
            'userId' => 'required',
            'media_id' => 'required',

        ]);

        $wechat = $this->wechat;
        $userId = $request->userId;
        $mediaId = $request->media_id;

        if ($validator->fails()) {
            return redirect('/masstest')
                ->withErrors($validator)
                ->withInput();

        } else {
            try {
                $user = UserSns::where('user_id', $userId)->where('status', '0')->first(['openid']);
                $openId = $user->openid;
            } catch (Exception $e) {
                return redirect('/masstest')
                    ->withErrors(array(0 => 'userId不正确/用户没有关注'))
                    ->withInput();
            }

            $broadcast = $wechat->broadcast; //var_dump($openId);die;
            // TEXT别名方式    
            $sendResult = $broadcast->previewImage($mediaId, $openId);//var_dump($sendResult);die;
            $msgId = '测试无消息id';

            if ($sendResult->errcode == 0) {
                $massLog = new MassLog();
                $massLog->number = 1;
                $massLog->msgId = $msgId;
                $massLog->receiver = 'userID' . $userId;
                $massLog->way = '测试群发';
                $massLog->contents = '临时图片媒体ID：' . $mediaId;
                $massLog->result = '提交成功';
                $massLog->save();
                return redirect('masstest')->with('mgssages', array(0 => '提交成功'));
            } else {
                $massLog = new MassLog();
                $massLog->number = 1;
                $massLog->msgId = $msgId;
                $massLog->receiver = 'userID' . $userId;
                $massLog->way = '测试群发';
                $massLog->contents = '临时图片媒体ID：' . $mediaId;
                $massLog->result = '提交失败';
                $massLog->save();
                return redirect('masstest')->with('mgssages', array(0 => '提交失败'));
            }
        }
    }

    /**
     * @param Request $request
     */

    public function preSendPictureText(Request $request)
    {
        if (!($request->select == 'test')) {
            echo 'Please select send to group';
            die;
        }

        $validator = Validator::make($request->all(), [
            'userId' => 'required',
            'media_id' => 'required',

        ]);

        $wechat = $this->wechat;
        $userId = $request->userId;
        $mediaId = $request->media_id;
        $arr = explode("#", $mediaId);
        $mediaId = $arr[0];

        $result = Media::where('id', $mediaId)->first(['bundle_id']);
        if ($result->bundle_id !== 0) {
            $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MIXED_MULTI'), $result->bundle_id));
        } else {
            $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MIXED'), $mediaId));
        }

        $title = $arr[1];


        if ($validator->fails()) {
            return redirect('/masstest')
                ->withErrors($validator)
                ->withInput();

        } else {
            try {
                $user = UserSns::where('user_id', $userId)->where('status', '0')->first(['openid']);
                $openId = $user->openid;
            } catch (Exception $e) {
                return redirect('/masstest')
                    ->withErrors(array(0 => 'userId不正确/用户没有关注'))
                    ->withInput();
            }

            $broadcast = $wechat->broadcast; //var_dump($openId);die;
            // TEXT别名方式
            $sendResult = $broadcast->previewNews($mediaId, $openId);//var_dump($sendResult);die;
            $msgId = '测试无消息id';

            if ($sendResult->errcode == 0) {
                $massLog = new MassLog();
                $massLog->number = 1;
                $massLog->msgId = $msgId;
                $massLog->receiver = 'userID' . $userId;
                $massLog->way = '测试群发';
                $massLog->contents = $title;
                $massLog->result = '提交成功';
                $massLog->save();
                return redirect('masstest')->with('mgssages', array(0 => '提交成功'));
            } else {
                $massLog = new MassLog();
                $massLog->number = 1;
                $massLog->msgId = $msgId;
                $massLog->receiver = 'userID' . $userId;
                $massLog->way = '测试群发';
                $massLog->contents = title;
                $massLog->result = '提交失败';
                $massLog->save();
                return redirect('masstest')->with('mgssages', array(0 => '提交失败'));
            }
        }

    }

    /**
     * 测试模板消息
     */
    public function pretemplate(Request $request)
    {
        $temp = new TemplateController();
        /**
         * 测试
         */
        $url = $request->url;
        $basic = Basic::where('name', 'TEMPLATEDATA')->first()->value;//模板数据
        $data = array_flip(explode(',', $basic));
        $datas = array(
            $request->first,
            $request->invest_product,
            $request->invest_profit,
            $request->remark
        );
        $i = 0;
        foreach ($data as $key => $value) {
            $data[$key] = $datas[$i++];
        }
        if (!empty($request->userId)) {
            $userdata = UserSns::select("openid")->where("user_id", $request->userId)->get()->toarray();
            $messtype = "测试群发";
            $msgid = "测试无消息id";
            $receiver = "Userid" . $request->userId;
        } else {
            $userdata = UserSns::select("openid")->get()->toarray();
            $messtype = "正式群发";
            $msgid = "正式无消息id";
            $receiver = "正式群发";
        }
        $messagedata = array(
            "小标题" => $request->first,
            "策略名称" => $request->invest_product,
            "操作风格" => $request->invest_style,
            "目前策略收益" => $request->invest_profit,
            "备注" => $request->remark,
            "点击跳转地址" => $request->url
        );
        $str = '';
        foreach ($messagedata as $key => $value) {
            $str .= $key . ":" . $value . " , ";
        }
        $res = $temp->group($url, $data, $userdata);
        $massLog = new MassLog();
        $massLog->msgId = $msgid;
        $massLog->receiver = $receiver;
        $massLog->way = $messtype;
        $massLog->contents = $str;
        $massLog->result = $res['errmsg'];
        $massLog->save();
        if ($res['errcode'] == '0') {
            return redirect()->back()->with('mgssages', array(0 => '提交成功'));
        } else {
            return redirect()->back()->with('mgssages', array(0 => '提交失败', 1 => $res['errmsg']));
        }
    }

    /**
     * 测试客服消息
     */
    public function precustomer(Request $request)
    {
        $way = ($request['url'] == '/massf') ? '正式客服消息' : '测试客服消息';
        if (isset($request['userId']) && $request['userId'] != '') {
            //客服消息单发 openid
            $user = UserSns::where('user_id', $request['userId'])->where('status', '0')->get(['openid']);
            $receiver = 'userID' . $request['userId'];
        } else {
            //客服消息群发 openid
            $user = UserSns::where('status', '0')->get(['openid']);
            $receiver = '关注且48小时有互动用户';
        }

        if (isset($request['text']) && $request['text'] != '') {
            //自己填写的文本
            $message = new Text(['content' => $request['text']]);
            $contents = $request['text'];
        } else if (isset($request['media_id']) && $request['media_id'] != '') {
            //判断类型
            $media = DB::table('media')->where('id', $request['media_id'])->first(['media_type', 'content']);
            $mId = explode("#", $request['media_id']);
            $request['media_id'] = $mId[0];
            switch ($media->media_type) {
                //图文消息
                case '1':
                    $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MIXED'), $request['media_id']));
                    $message = new Material('mpnews', $mediaId);
                    $contents = $mId[1];
                    break;
                case '2':
                    $bid = DB::table('media')->where('id', $request['media_id'])->first(['bundle_id']);
                    $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MIXED_MULTI'), $bid->bundle_id));
                    $message = new Material('mpnews', $mediaId);
                    $contents = $mId[1];
                    break;
                case '3':
                    $mediaId = Redis::GET(sprintf(config('app.redisKey.KEY_WEIXIN_MEDIA'), $request['media_id']));
                    $message = new Material('image', $mediaId);
                    $contents = '图片' . $request['media_id'];
                    break;
                case '4':
                    return redirect($request['url'])->with('mgssages', array(0 => '发送音频正在开发中'));
                    //Todo
                    break;
                case '5':
                    return redirect($request['url'])->with('mgssages', array(0 => '发送视频正在开发中'));
                    //Todo
                    break;
                case '6':
                    $message = new Text(['content' => $media->content]);
                    $contents = $media->content;
                    break;
                default:
                    break;
            }
        } else {
            $massLog = new MassLog();
            $massLog->number = 0;
            $massLog->msgId = '';
            $massLog->receiver = $receiver;
            $massLog->way = $way;
            $massLog->contents = $contents;
            $massLog->result = '提交失败';
            $massLog->save();

            return redirect($request['url'])->with('mgssages', array(0 => '提交失败'));
        }
        //发送客服消息
        $staff = $this->wechat->staff; // 客服管理
        $num = 0;
        foreach ($user as $v) {
            try {
                if ($staff->message($message)->to($v->openid)->send())
                    $num++;
            } catch (Exception $e) {
//                return redirect($request['url'])->with('mgssages', array(0 => '提交失败'));
            }
        }
        $massLog = new MassLog();
        $massLog->number = $num;
        $massLog->msgId = '';
        $massLog->receiver = $receiver;
        $massLog->way = $way;
        $massLog->contents = $contents;
        $massLog->result = '提交成功';
        $massLog->save();

        return redirect($request['url'])->with('mgssages', array(0 => '提交成功'));
    }
}
