<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadController extends Controller
{
    public function uploadFile(Request $request)
    {
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $user = auth()->user();
        $userUrl = $user->url;
        $ext = $file->getClientOriginalExtension();
        $mimeType = $file->getClientMimeType();
        $file->move(storage_path("app/public/user/{$userUrl}"), $fileName);
        $imagePath = "/user/{$userUrl}/{$fileName}";

        $media = new Media();
        $media->name = $fileName;
        $media->url = $imagePath;
        $media->type = 'image';
        $media->size = $file->getSize();
        $media->extension = $ext;
        $media->mime_type = $mimeType;
        $media->user_id = $user->id;
        $media->object_type = $request->object_type ?? null;
        $media->object_id = $request->object_id ?? null;
        $media->save();
        return $this->sendRespondSuccess($media->id);
    }

    public function storage($url)
    {
        try {
            $path = storage_path('app/auth/' . $url);
            $file = File::get($path);
            $mimeType = File::mimeType($path);
            $response = response()->make($file, 200);
            $response->header("Content-Type", $mimeType);
            return $response;
        } catch (\Exception $e) {
            die;
        }
    }
}
