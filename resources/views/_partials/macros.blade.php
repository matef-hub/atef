@php
  // قمت بزيادة الحجم الافتراضي قليلاً ليكون أوضح
  $width = $width ?? '40';
  $height = $height ?? '40';
@endphp

<span class="app-brand-logo demo">
  <svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 48 48" fill="none"
    xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="qas_bleu_grad" x1="24" y1="4" x2="24" y2="44"
        gradientUnits="userSpaceOnUse">
        <stop stop-color="#2F80ED" />
        <stop offset="1" stop-color="#1F3A93" />
      </linearGradient>

      <linearGradient id="qas_gold_grad" x1="38" y1="12" x2="38" y2="36"
        gradientUnits="userSpaceOnUse">
        <stop stop-color="#F2C94C" />
        <stop offset="1" stop-color="#F2994A" />
      </linearGradient>
    </defs>

    <path d="M24 4V16M24 16V44M24 16L12 28M24 16L36 28M24 4L28 10H20L24 4Z" stroke="url(#qas_bleu_grad)"
      stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

    <path d="M8 32C8 34.2091 9.79086 36 12 36C14.2091 36 16 34.2091 16 32" stroke="url(#qas_bleu_grad)" stroke-width="2"
      stroke-linecap="round" />
    <path d="M32 32C32 34.2091 33.7909 36 36 36C38.2091 36 40 34.2091 40 32" stroke="url(#qas_bleu_grad)"
      stroke-width="2" stroke-linecap="round" />

    <path
      d="M40 12C42.2091 12 44 13.7909 44 16C44 18.2091 42.2091 20 40 20M40 12V20M40 12C37.7909 12 36 13.7909 36 16C36 18.2091 37.7909 20 40 20M36 16H24M44 16H24"
      stroke="url(#qas_gold_grad)" stroke-width="1.5" stroke-linecap="round" />
    <path d="M40 28V36M40 28C42.2091 28 44 29.7909 44 32C44 34.2091 42.2091 36 40 36M40 36H24"
      stroke="url(#qas_gold_grad)" stroke-width="1.5" stroke-linecap="round" />

    <circle cx="24" cy="16" r="2" fill="url(#qas_bleu_grad)" />
  </svg>
</span>
