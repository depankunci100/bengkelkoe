@props([
    'headers' => [],
    'hover' => true,
    'striped' => false,
])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table align-middle mb-0' . ($hover ? ' table-hover' : '') . ($striped ? ' table-striped' : '')]) }}>
        @if(!empty($headers))
            <thead class="table-light text-uppercase fs-7 text-muted border-bottom">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="py-3 px-3">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
