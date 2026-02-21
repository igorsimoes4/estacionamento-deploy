(function () {
    var cards = document.querySelectorAll('.card, .theme-kpi, .theme-quick-link');
    if (!('IntersectionObserver' in window)) {
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.style.transform = 'translateY(0)';
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    cards.forEach(function (card) {
        card.style.transform = 'translateY(8px)';
        card.style.opacity = '.01';
        card.style.transition = 'all .35s ease';
        observer.observe(card);
    });
})();
