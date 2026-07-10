<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GoogleCloudStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Throwable;

class StorageWebController extends Controller
{
    public function __construct(
        private readonly GoogleCloudStorageService $storage,
    ) {
    }

    public function index(): View
    {
        return view('store.storage', [
            'bucketName' => (string) config('services.gcs.bucket', 'almacenamientopollito'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'max:51200'],
        ], [
            'archivo.required' => 'Selecciona un archivo para subir.',
            'archivo.uploaded' => 'No se pudo recibir el archivo. Revisa que no supere los 50 MB y vuelve a intentarlo.',
            'archivo.max' => 'El archivo no debe superar los 50 MB.',
        ]);

        try {
            $uploaded = $this->storage->upload($request->file('archivo'));
            $uploaded['download_url'] = URL::temporarySignedRoute(
                'store.storage.download',
                now()->addMinutes((int) config('services.gcs.signed_url_ttl', 60)),
                ['object' => $uploaded['object']]
            );
        } catch (Throwable $exception) {
            return back()
                ->withInput($request->except('archivo'))
                ->with('error', 'No se pudo subir el archivo a Google Cloud Storage: '.$exception->getMessage());
        }

        return back()
            ->with('success', 'Archivo subido correctamente a Google Cloud Storage.')
            ->with('gcs_uploaded', $uploaded);
    }

    public function download(Request $request): Response|RedirectResponse
    {
        try {
            $download = $this->storage->download((string) $request->query('object', ''));
        } catch (Throwable $exception) {
            return redirect()
                ->route('store.storage')
                ->with('error', 'No se pudo descargar el archivo: '.$exception->getMessage());
        }

        $filename = str_replace('"', '', (string) $download['filename']);

        return response($download['contents'], 200, [
            'Content-Type' => (string) $download['content_type'],
            'Content-Disposition' => 'attachment; filename="'.$filename.'"; filename*=UTF-8\'\''.rawurlencode($filename),
            'Content-Length' => (string) strlen((string) $download['contents']),
        ]);
    }
}