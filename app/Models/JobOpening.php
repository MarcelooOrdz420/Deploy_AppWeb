<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JobOpening extends Model
{
    protected $fillable=['title','description','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
