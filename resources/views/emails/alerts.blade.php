@component('mail::message')
# Hello, {{ $user->name }}

Here are the latest grants matching your saved search criteria:

@if(count($grants) > 0)
@foreach ($grants as $grant)
---

### {{ $grant['opportunity_title'] }}  
**Opportunity ID**: {{ $grant['opportunity_id'] }}  
**Ceiling**: {{ number_format($grant['award_ceiling'], 2) ?? 'N/A' }}  
**Close Date**: {{ \Carbon\Carbon::parse($grant['close_date'])->format('M d, Y') ?? 'N/A' }}  
**Description**: {{ \Illuminate\Support\Str::limit($grant['description'], 200) }}  

[View Full Details]({{ config('app.url') }}/?grants={{ $grant['opportunity_id'] }})

@endforeach
---

@php
$grantIds = implode(',', array_column($grants, 'opportunity_id'));
@endphp

Link to all results: [View All Results]({{ config('app.url') }}/?grants={{ $grantIds }})

@else
No grants were found matching your criteria this time. Keep an eye out for more opportunities in the future!
@endif

@component('mail::button', ['url' => url('/profile')])
Update Your Search Preferences
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
