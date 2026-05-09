@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register Page')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-6">
        <!-- Register -->
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
            <h4 class="mb-1">Adventure starts here 🚀</h4>
            <p class="mb-6">Make your app management easy and fun!</p>

            <form id="formAuthentication" class="mb-4" action="{{ route('register') }}" method="POST">
              @csrf
              <div class="mb-6 form-control-validation">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="username"
                  name="name" placeholder="johndoe" autofocus value="{{ old('name') }}" autocomplete="username" />
                @error('name')
                  <span class="invalid-feedback" role="alert">
                    <span class="fw-medium">{{ $message }}</span>
                  </span>
                @enderror
              </div>
              <div class="mb-6 form-control-validation">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                  name="email" placeholder="john@example.com" value="{{ old('email') }}" autocomplete="email" />
                @error('email')
                  <span class="invalid-feedback" role="alert">
                    <span class="fw-medium">{{ $message }}</span>
                  </span>
                @enderror
              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                    name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" autocomplete="new-password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
                @error('password')
                  <span class="invalid-feedback" role="alert">
                    <span class="fw-medium">{{ $message }}</span>
                  </span>
                @enderror
              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password-confirm">Confirm Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password-confirm" class="form-control" name="password_confirmation"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" autocomplete="new-password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
              </div>
              @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mb-6 mt-8">
                  <div class="form-check mb-8 ms-2 @error('terms') is-invalid @enderror">
                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms"
                      name="terms" autocomplete="off" />
                    <label class="form-check-label" for="terms">
                      I agree to
                      <a href="{{ route('policy.show') }}" target="_blank">privacy policy</a> &
                      <a href="{{ route('terms.show') }}" target="_blank">terms</a>
                    </label>
                  </div>
                  @error('terms')
                    <div class="invalid-feedback" role="alert">
                      <span class="fw-medium">{{ $message }}</span>
                    </div>
                  @enderror
                </div>
              @endif
              <div class="mb-6">
                <button type="submit" class="btn btn-primary d-grid w-100">Sign up</button>
              </div>
            </form>

            <p class="text-center">
              <span>Already have an account?</span>
              @if (Route::has('login'))
                <a href="{{ route('login') }}">
                  <span>Sign in instead</span>
                </a>
              @endif
            </p>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>
  </div>
@endsection
