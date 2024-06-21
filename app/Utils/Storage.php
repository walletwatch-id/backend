<?php

namespace App\Utils;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Symfony\Component\Uid\Uuid;

class Storage
{
    public static function store(
        UploadedFile $file,
        string $path,
        ?string $name = null,
        string $disk = 'local',
    ) {
        if (is_null($name)) {
            $name = Uuid::v7().'.'.$file->extension();
        }

        $file->storeAs($path, $name, $disk);

        return json_encode([
            'disk' => $disk,
            'path' => $path,
            'name' => $name,
        ]);
    }

    public static function get(string $id)
    {
        $params = json_decode($id);
        $disk = $params->disk;
        $path = $params->path;
        $name = $params->name;

        return StorageFacade::disk($disk)->path($path.'/'.$name);
    }

    public static function delete(string $id)
    {
        $params = json_decode($id);
        $disk = $params->disk;
        $path = $params->path;
        $name = $params->name;

        return StorageFacade::disk($disk)->delete($path.'/'.$name);
    }
}
