<?php

namespace App\Http\Controllers\libs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Facades\Image;

class ImageManagement extends ServiceProvider
{
    public function __construct()
    {
        Log::info('Image Management Started.');
    }

    public static function resize($path)
    {
        $width = 640; // your max width
        $height = 640; // your max height

        try {
            $img = Image::make($path);
            if ($img->height() > $img->width()) {
                $width = null;
            } else {
                $height = null;
            }
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($path, 75, 'png');
        } catch (\Exception $exception) {
        }
    }
}
