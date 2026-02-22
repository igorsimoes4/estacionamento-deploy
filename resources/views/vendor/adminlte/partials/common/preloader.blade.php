@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

<div class="{{ $preloaderHelper->makePreloaderClasses() }} theme-preloader-shell" style="{{ $preloaderHelper->makePreloaderStyle() }}">
    <div class="theme-preloader-panel">
        <div class="theme-preloader-logo-wrap">
            <img src="{{ asset(config('adminlte.preloader.img.path', 'vendor/adminlte/dist/img/AdminLTELogo.png')) }}"
                class="img-circle {{ config('adminlte.preloader.img.effect', 'animation__shake') }}"
                alt="{{ config('adminlte.preloader.img.alt', 'AdminLTE Preloader Image') }}"
                width="{{ config('adminlte.preloader.img.width', 60) }}"
                height="{{ config('adminlte.preloader.img.height', 60) }}"
                style="animation-iteration-count: infinite;">
        </div>
        <h3 class="theme-preloader-title">Carregando painel</h3>
        <p class="theme-preloader-subtitle">Preparando dados e componentes do sistema...</p>
        <div class="theme-preloader-progress" aria-hidden="true">
            <span></span>
        </div>
    </div>
</div>

