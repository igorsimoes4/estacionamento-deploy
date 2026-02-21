<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('monthly-subscribers.*') ? 'active' : '' }}" 
       href="{{ route('monthly-subscribers.index') }}">
        <i class="fas fa-users"></i> Mensalistas
    </a>
</li> 
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('accounting.*') ? 'active' : '' }}"
       href="{{ route('accounting.index') }}">
        <i class="fas fa-calculator"></i> Contabilidade
    </a>
</li>
