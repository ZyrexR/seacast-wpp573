@props([
    'headers' => [],
])

<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-[#244456] bg-white dark:bg-[#0D2B3E] shadow-sm">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-xs border-collapse']) }}>
        @if(!empty($headers))
            <thead class="bg-slate-50 dark:bg-[#091F30] border-b border-slate-200 dark:border-[#244456] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-floor">
                <tr>
                    @foreach($headers as $h)
                        <th scope="col" class="px-5 py-3.5">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 dark:divide-[#244456]/60 text-slate-700 dark:text-slate-200">
            {{ $slot }}
        </tbody>
    </table>
</div>

