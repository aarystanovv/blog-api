<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    public function uploadImage(UploadedFile $image, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);
        return $image->storeAs($folder, $name.'.'.$image->getClientOriginalExtension(), $disk);
    }
}
