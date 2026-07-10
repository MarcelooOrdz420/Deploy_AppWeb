<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GoogleCloudStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'archivo' => ['required', 'file', 'max:10240'],
        ], [
            'archivo.required' => 'Selecciona un archivo para subir.',
            'archivo.uploaded' => 'No se pudo recibir el archivo. Revisa que no supere los 10 MB y vuelve a intentarlo.',
            'archivo.max' => 'El archivo no debe superar los 10 MB.',
        ]);

        try {
            $uploaded = $this->storage->upload($request->file('archivo'));
        } catch (Throwable $exception) {
            return back()
                ->withInput($request->except('archivo'))
                ->with('error', 'No se pudo subir el archivo a Google Cloud Storage: '.$exception->getMessage());
        }

        return back()
            ->with('success', 'Archivo subido correctamente a Google Cloud Storage.')
            ->with('gcs_uploaded', $uploaded);
    }
}