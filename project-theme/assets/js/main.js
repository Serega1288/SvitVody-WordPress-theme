document.addEventListener('DOMContentLoaded', () => {
    if (typeof Swup === 'undefined') {
        return;
    }

    new Swup({
        containers: ['#swup']
    });
});
