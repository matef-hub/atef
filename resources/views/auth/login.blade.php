@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-6">
        <!-- Login -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-6">
              <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">@include('_partials.macros')</span>
                <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">إهلا بك فى {{ config('variables.templateName') }}! 👋</h4>
            <p class="mb-6">من فضلك سجل بياناتك للدخول للشاشة الرئيسية</p>

            @if (session('status'))
              <div class="alert alert-success mb-1 rounded-0" role="alert">
                <div class="alert-body">
                  {{ session('status') }}
                </div>
              </div>
            @endif

            <form id="formAuthentication" class="mb-4" action="{{ route('login') }}" method="POST">
              @csrf
              <div class="mb-6 form-control-validation">
                <label for="email" class="form-label">الأيميل الالكتروني</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                  name="email" placeholder="john@example.com" autofocus value="{{ old('email') }}"
                  autocomplete="username" />
                @error('email')
                  <span class="invalid-feedback" role="alert">
                    <span class="fw-medium">{{ $message }}</span>
                  </span>
                @enderror
              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password">كلمة المرور</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                    name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
                @error('password')
                  <span class="invalid-feedback" role="alert">
                    <span class="fw-medium">{{ $message }}</span>
                  </span>
                @enderror
              </div>
              <div class="my-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                      {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label" for="remember-me"> تذكرني </label>
                  </div>
                  @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                      <p class="mb-0">نسيت كلمة المرور؟</p>
                    </a>
                  @endif
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">تسجيل الدخول</button>
              </div>
            </form>

            <p class="text-center">
              <span>لتسجيل حساب جديد؟</span>
              @if (Route::has('register'))
                <a href="{{ route('register') }}">
                  <span>إنشاء حساب</span>
                </a>
              @endif
            </p>
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>
  </div>
@endsection
