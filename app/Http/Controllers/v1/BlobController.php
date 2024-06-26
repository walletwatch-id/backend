<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blob\BlobRequest;
use App\Utils\Encoder;
use App\Utils\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class BlobController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(BlobRequest $request, string $id)
    {
        try {
            $decoded_id = Encoder::base64UrlDecode($id);
            $name = $request->query('name') ?? json_decode($decoded_id)->name;
            $file_path = Storage::get($decoded_id);

            if ($request->boolean('force_download')) {
                return response()->download($file_path, $name);
            } else {
                return response()
                    ->file($file_path)
                    ->setContentDisposition('inline', $name);
            }
        } catch (Throwable $e) {
            if (config('app.debug')) {
                throw $e;
            }

            throw new NotFoundHttpException('Blob not found.');
        }
    }
}
