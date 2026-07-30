<?php
namespace App\Http\Requests\Auth;
use App\Enums\Role; use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class LoginRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['email'=>['required','email'],'password'=>['required','string'],'expected_role'=>['nullable',Rule::enum(Role::class)]];}}
