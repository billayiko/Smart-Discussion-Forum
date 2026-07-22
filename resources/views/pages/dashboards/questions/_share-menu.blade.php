@php
    $shareText = $shareText ?? '';
    $buttonClass = $buttonClass ?? 'forum-icon-btn';
    $buttonLabel = $buttonLabel ?? '';
@endphp
<details class="forum-share">
    <summary class="{{ $buttonClass }}" title="Share"><i class="fas fa-share-nodes"></i>@if($buttonLabel) {{ $buttonLabel }}@endif</summary>
    <div class="forum-share-menu">
        <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener"><i class="fab fa-twitter"></i> Twitter / X</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"><i class="fab fa-facebook"></i> Facebook</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"><i class="fab fa-linkedin"></i> LinkedIn</a>
        <a href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        <button type="button" class="forum-copy-link-btn" data-share-url="{{ $shareUrl }}"><i class="fas fa-link"></i> Copy Link</button>
    </div>
</details>
