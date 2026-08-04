document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', function () {
    document.querySelector('#admin-sidebar')?.classList.toggle('is-open');
});

document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!window.confirm(form.dataset.confirm || '確定執行？')) event.preventDefault();
    });
});
