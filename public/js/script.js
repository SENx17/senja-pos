$(window).on('load', function () {
    changeWidthTitle();
})


document.addEventListener('set-width-title', () => {

    $(() => {
        changeWidthTitle();
    });

});


Livewire.on('show-toast', ({type, title, message, onYes, onNo}) => {
    console.log(type);
    switch (type) {
        case 'success':
            Toast.showSuccess(document.body, title, message);
            break;
        case 'error':
            Toast.showError(document.body, title, message);
            break;
        case 'info':
            Toast.showInfo(document.body, title, message);
            break;
        case 'confirm':
            Popup.confirm(title, message, 'warning', 'Oke', 'Batal', onYes, onNo);
            break;
        default:
            console.error('Tipe toast tidak valid');
    }
});


/**
 * fungis ini digunakan untuk memberikan underline dititle navbar
 */
function changeWidthTitle() {
    let widthTitleNavbar = $(".navbar-title").width();
    let offset = $(".navbar-title").offset().left;

    $("#title-divider").css({
        position: "relative",
        left: offset - 270,
    });
    $("#title-divider").width(widthTitleNavbar);
}

document.addEventListener('update-menu', function () {
    // Ambil URL saat ini
    var currentURL = window.location.pathname;

    // Jika currentURL lebih dari 2, ambil hanya 2 data pertama
    var urlParts = currentURL.split('/').filter(Boolean);
    if (urlParts.length > 2) {
        currentURL = '/' + urlParts.slice(0, 2).join('/');
    }

    // Dapatkan semua elemen 'a' dalam Accordion
    var accordionLinks = $('.accordion-item a');

    accordionLinks.each(function () {
        if ($(this).attr('href') === currentURL) {
            const accordionBtn = $(this).closest('.accordion-item').find('.accordion-button');
            const collapseBtn = $(this).closest('.accordion-collapse');
            const itemBtn = $(this).find('button');

            collapseBtn.addClass('show');
            itemBtn.addClass('inner-menu-active');
            accordionBtn.attr('aria-expanded', 'true');
            accordionBtn.removeClass('collapsed');
        }
    });
});
