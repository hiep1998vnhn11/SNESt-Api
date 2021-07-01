<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Like;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use App\Models\Thresh;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version0X;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;

class ServerController extends Controller
{
    public function index(Request $request)
    {
        // $details = [
        //     'datetime' => now(),
        //     'title' => 'Snest - đăng ký',
        //     'header' => 'Cảm ơn bạn đã đăng ký vào Snest',
        //     'content' => 'Đây là mã xác minh của bạn: ' . 123456
        // ];
        // Mail::to('hiep.tv167170@sis.hust.edu.vn')->send(new RegisterMail($details));
        return view('welcome');
    }

    public function api()
    {
        $client = new Client(new Version0X('http://localhost:5000'));
        $client->initialize();
        $client->emit('broadcast', ['foo' => 'bar']);
        $client->close();
    }
}
