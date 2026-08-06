@php
    $siteName = \App\Models\AppSetting::get('app_name', config('app.name'));
@endphp

<tr>
<td>
<table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
<p>{{ $siteName }} — {{ date('Y') }}</p>
</td>
</tr>
</table>
</td>
</tr>
