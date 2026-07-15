<header class="pulse-topbar">
    <div class="pulse-title">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
    </div>
</header>

@if (session('success'))
    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color:#d33;">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
