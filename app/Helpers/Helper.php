<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Helper
{
    //! File or Image Upload
    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $imageName = Str::slug($name) . '.' . $file->extension();
        $path      = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $file->move($path, $imageName);

        // Compress image to 200 KB if it is an image
        $fullPath = $path . '/' . $imageName;
        self::compressImage($fullPath);

        return 'uploads/' . $folder . '/' . $imageName;
    }

    public static function compressImage(string $filePath): void
    {
        if (!file_exists($filePath)) {
            return;
        }

        $mime = @mime_content_type($filePath);
        if (!$mime) {
            return;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($mime, $allowedMimes)) {
            return;
        }

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($filePath);

            $targetBytes = 200 * 1024; // 200 KB
            $currentSize = filesize($filePath);

            if ($currentSize <= $targetBytes) {
                return;
            }

            // 1. Initial Scale Down if the image is exceptionally large (e.g. > 1600px width/height)
            $maxWidth = 1600;
            $maxHeight = 1600;
            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $image->scaleDown(width: $maxWidth, height: $maxHeight);
            }

            // 2. Try to save with reduced quality iteratively
            $quality = 90;
            $image->save($filePath, $quality);
            $currentSize = filesize($filePath);

            while ($currentSize > $targetBytes && $quality > 15) {
                $quality -= 10;
                $image->save($filePath, $quality);
                $currentSize = filesize($filePath);
            }

            // 3. If quality reduction is not enough, scale down further iteratively
            if ($currentSize > $targetBytes) {
                $scale = 0.8;
                while ($currentSize > $targetBytes && $scale >= 0.3) {
                    $newWidth = (int)($image->width() * $scale);
                    $newHeight = (int)($image->height() * $scale);

                    $cloned = clone $image;
                    $cloned->resize($newWidth, $newHeight);
                    $cloned->save($filePath, $quality);
                    $currentSize = filesize($filePath);

                    $scale -= 0.15;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Image compression failed for ' . $filePath . ': ' . $e->getMessage());
        }
    }

    //! File or Image Delete
    public static function fileDelete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    //! Generate Slug
    public static function makeSlug($model, string $title): string
    {
        $slug = Str::slug($title);
        while ($model::where('slug', $slug)->exists()) {
            $randomString = Str::random(5);
            $slug         = Str::slug($title) . '-' . $randomString;
        }
        return $slug;
    }

    //! JSON Response
    public static function jsonResponse(bool $status, string $message, int $code, $data = null, bool $paginate = false, $paginateData = null): JsonResponse
    {
        $response = [
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
        ];
        if ($paginate && !empty($paginateData)) {
            $response['data'] = $data;
            $response['pagination'] = [
                'current_page' => $paginateData->currentPage(),
                'last_page' => $paginateData->lastPage(),
                'per_page' => $paginateData->perPage(),
                'total' => $paginateData->total(),
                'first_page_url' => $paginateData->url(1),
                'last_page_url' => $paginateData->url($paginateData->lastPage()),
                'next_page_url' => $paginateData->nextPageUrl(),
                'prev_page_url' => $paginateData->previousPageUrl(),
                'from' => $paginateData->firstItem(),
                'to' => $paginateData->lastItem(),
                'path' => $paginateData->path(),
            ];
        } elseif ($paginate && !empty($data)) {
            $response['data'] = $data->items();
            $response['pagination'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'first_page_url' => $data->url(1),
                'last_page_url' => $data->url($data->lastPage()),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'path' => $data->path(),
            ];
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public static function jsonErrorResponse($message = null, $code = null, $errors = 'Error'): JsonResponse
    {
        $arr = $message;
        $arryerrors = [];

       if(is_array($message)){
            foreach ($arr  as $key => $value) {
            $arryerrors[] = [
                'field'   => $key,
                'message' => $value[0],
            ];
        }
       }
       else{
       if($code == 401){
        $errors ='Your session has expired. Please sign in again.';
       }elseif($code == 403){
        $errors = 'Forbidden';
       }elseif($code == 404){
        $errors = 'Not Found';
       }elseif($code == 500){
        $errors = $message ;
       }
       }

        $response = [
            'status'  => false,
            'code'    => $code,
            'message' => $errors,
            'errors' => $arryerrors,
        ];
        return response()->json($response, $code);
    }
    public static function sendNotifyMobile($token, $notifyData): void
    {
        try {
            $factory = (new Factory)->withServiceAccount(storage_path(config('firebase.credentials')));
            $messaging = $factory->createMessaging();
            $notification = Notification::create($notifyData['title'], Str::limit($notifyData['body'], 100), $notifyData['icon']);
            $message = CloudMessage::withTarget('token', $token)->withNotification($notification);
            $messaging->send($message);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
        return;
    }

    /* public static function Translate($model, $str){
        $data = $model::where('key', $str)->first();
        if($data){
            return $data->value;
        }
        return $str;
    } */
}
