(function () {
    function hasWireDirective(element) {
        if (!element || !element.attributes) {
            return false;
        }

        for (var i = 0; i < element.attributes.length; i++) {
            var name = element.attributes[i].name || '';
            if (name.indexOf('wire:') === 0 || name.indexOf('x-on:') === 0) {
                return true;
            }
        }

        return false;
    }

    var loader = document.getElementById('theme-page-loader');

    function showPageLoader() {
        if (!loader) {
            return;
        }

        loader.classList.add('is-active');
        document.body.classList.add('theme-nav-loading');
    }

    function hidePageLoader() {
        if (!loader) {
            return;
        }

        loader.classList.remove('is-active');
        document.body.classList.remove('theme-nav-loading');
    }

    function isSamePageNavigation(url) {
        return url.pathname === window.location.pathname && url.search === window.location.search;
    }

    function shouldHandleLink(event, link) {
        if (!link || event.defaultPrevented || event.button !== 0) {
            return false;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return false;
        }

        var href = link.getAttribute('href') || '';
        if (href === '' || href === '#' || href.indexOf('javascript:') === 0) {
            return false;
        }

        if (link.hasAttribute('download') || link.target === '_blank') {
            return false;
        }

        if (link.hasAttribute('data-no-loader') || link.classList.contains('no-loader')) {
            return false;
        }

        if (hasWireDirective(link)) {
            return false;
        }

        var linkUrl;
        try {
            linkUrl = new URL(link.href, window.location.href);
        } catch (e) {
            return false;
        }

        if (linkUrl.origin !== window.location.origin) {
            return false;
        }

        if (isSamePageNavigation(linkUrl) && linkUrl.hash !== '') {
            return false;
        }

        return true;
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a');
        if (!shouldHandleLink(event, link)) {
            return;
        }

        showPageLoader();
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.hasAttribute('data-no-loader') || form.target === '_blank' || hasWireDirective(form)) {
            return;
        }

        showPageLoader();
    }, true);

    window.addEventListener('pageshow', hidePageLoader);
    window.addEventListener('load', function () {
        hidePageLoader();
        document.body.classList.add('theme-ready');
    });

    function initScrollReveal() {
        var cards = document.querySelectorAll('.card, .theme-kpi, .theme-quick-link');
        if (!cards.length || !('IntersectionObserver' in window)) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('theme-fade-in');
                entry.target.style.transform = 'translateY(0)';
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08 });

        cards.forEach(function (card) {
            card.style.transform = 'translateY(8px)';
            card.style.opacity = '.01';
            card.style.transition = 'opacity .35s ease, transform .35s ease';
            observer.observe(card);
        });
    }

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(initScrollReveal, { timeout: 700 });
    } else {
        setTimeout(initScrollReveal, 130);
    }
})();
