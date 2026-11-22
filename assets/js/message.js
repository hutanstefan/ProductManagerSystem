var flashMessage = document.querySelector('.flash-message');

if (flashMessage) {
    setTimeout(function () {
        flashMessage.style.display = 'none';
    }, 5000);
}
