<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('monthly-subscribers.*') ? 'active' : '' }}" 
       href="{{ route('monthly-subscribers.index') }}">
        <i class="fas fa-users"></i> Mensalistas
    </a>
</li> 