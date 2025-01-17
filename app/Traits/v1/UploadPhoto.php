<?php

namespace App\Traits\v1;

use Illuminate\Support\Str;

trait UploadPhoto
{
    public function uploadPhoto($file, $path)
    {
        $customName = 'laravel_' . Str::uuid();
        $ext = $file->getClientOriginalExtension();
        $fileName = "$customName.$ext";

        $storedPath = $file->storeAs($path, $fileName, 'public');

        return "/uploads/$storedPath";
    }
}
