<div class="theme-fade-in" wire:poll.25s>
    <div class="row mb-4">
        @foreach ($cards as $card)
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="theme-kpi {{ $card['class'] }}">
                    <div>
                        <p>{{ $card['label'] }}</p>
                        <h3>{{ $card['value'] }}</h3>
                    </div>
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="theme-panel h-100">
                <p class="mb-1">Entradas hoje</p>
                <h4>{{ $entriesToday }}</h4>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="theme-panel h-100">
                <p class="mb-1">Saidas hoje</p>
                <h4>{{ $exitsToday }}</h4>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="theme-panel h-100">
                <p class="mb-1">Receita hoje</p>
                <h4>R$ {{ number_format($revenueToday, 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>
