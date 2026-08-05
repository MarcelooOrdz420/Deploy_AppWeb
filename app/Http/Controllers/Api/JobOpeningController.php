<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
class JobOpeningController extends Controller
{
    public function index(): JsonResponse { return response()->json(Schema::hasTable('job_openings') ? JobOpening::query()->where('is_active',true)->latest()->get() : []); }
    public function adminIndex(): JsonResponse { return response()->json(Schema::hasTable('job_openings') ? JobOpening::query()->latest()->get() : []); }
    public function store(Request $request): JsonResponse { if(!Schema::hasTable('job_openings')) return response()->json(['message'=>'Integra primero la tabla job_openings en MariaDB.'],503); $data=$request->validate(['title'=>['required','string','max:120'],'description'=>['nullable','string','max:500'],'is_active'=>['nullable','boolean']]); return response()->json(JobOpening::create($data),201); }
    public function destroy(JobOpening $jobOpening): JsonResponse { $jobOpening->delete(); return response()->json(['message'=>'Vacante eliminada.']); }
}
