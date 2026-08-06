@php
    $siteLogoPath = \App\Models\AppSetting::get('logo_path');
    $logoUrl = $siteLogoPath ? asset('storage/' . $siteLogoPath) : asset('images/email-logo.png');
@endphp

<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logoUrl }}" class="logo" alt="{{ $slot }}"><span>{{ $slot }}</span>
</a>
</td>
</tr>
