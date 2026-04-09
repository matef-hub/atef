@php
  $currentPhotoName = $this->user->profile_photo_path ? basename($this->user->profile_photo_path) : null;
@endphp

<x-form-section submit="updateProfileInformation" has-files="true" novalidate="true">
  <x-slot name="title">
    {{ __('Profile Information') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Update your account\'s profile information and email address.') }}
  </x-slot>

  <x-slot name="form">

    <x-action-message on="saved">
      {{ __('Saved.') }}
    </x-action-message>

    <!-- Profile Photo -->
    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
    <div class="mb-6" x-data="{ photoName: @js($currentPhotoName), photoPreview: null }">
      <div class="input-group legal-contract-file-group">
        <!-- Profile Photo File Input -->
        <input id="photo-input" type="file" name="photo" class="d-none" accept="image/*" aria-label="رفع الملف"
          wire:model.live="photo" x-ref="photo"
          x-on:change="photoName = $refs.photo.files[0]?.name || @js($currentPhotoName); if (!$refs.photo.files[0]) { photoPreview = null; return; } const reader = new FileReader(); reader.onload = event => { photoPreview = event.target.result; }; reader.readAsDataURL($refs.photo.files[0]);" />

        <span id="photo-file-name" class="form-control legal-contract-file-name @error('photo') is-invalid @enderror"
          x-bind:title="photoName || 'لم يتم اختيار ملف بعد.'" aria-live="polite"
          x-text="photoName || 'لم يتم اختيار ملف بعد.'">
          {{ $currentPhotoName ?: 'لم يتم اختيار ملف بعد.' }}
        </span>

        <button type="button" class="btn btn-primary" x-on:click.prevent="$refs.photo.click()"
          x-bind:aria-label="photoName ? 'استبدال الملف' : 'اختيار ملف'">
          <i class="icon-base ti tabler-upload icon-16px" aria-hidden="true"></i>
          <span x-text="photoName ? 'استبدال' : 'اختيار'">{{ $currentPhotoName ? 'استبدال' : 'اختيار' }}</span>
        </button>
      </div>

      <small class="text-muted d-block mt-1">الملفات المدعومة: JPG / JPEG / PNG / WEBP — الحد الأقصى: 10 ميجابايت</small>

      <!-- Current Profile Photo -->
      <div class="mt-2" x-show="! photoPreview">
        <img src="{{ $this->user->profile_photo_url }}" class="rounded-circle" height="80px" width="80px">
      </div>

      <!-- New Profile Photo Preview -->
      <div class="mt-2" x-show="photoPreview">
        <img x-bind:src="photoPreview" class="rounded-circle" width="80px" height="80px">
      </div>

      @if ($this->user->profile_photo_path)
      <button type="button" class="btn btn-danger mt-2" wire:click="deleteProfilePhoto">
        {{ __('Remove Photo') }}
      </button>
      @endif

      <x-input-error for="photo" class="mt-2" />
    </div>
    @endif

    <!-- Name -->
    <div class="mb-6">
      <x-label class="form-label" for="name" value="{{ __('Name') }}" />
      <x-input id="name" type="text" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" wire:model="state.name"
        autocomplete="name" />
      <x-input-error for="name" />
    </div>

    <!-- Email -->
    <div class="mb-6">
      <x-label class="form-label" for="email" value="{{ __('Email') }}" />
      <x-input id="email" type="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
        wire:model="state.email" />
      <x-input-error for="email" />
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="d-flex align-items-baseline">
      <x-button>
        {{ __('Save') }}
      </x-button>
    </div>
  </x-slot>
</x-form-section>
