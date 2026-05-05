document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("productContainer");

    function slideLeft() {
        container.scrollLeft -= container.clientWidth;
    }

    function slideRight() {
        container.scrollLeft += container.clientWidth;
    }

    window.slideLeft = slideLeft;
    window.slideRight = slideRight;

    // tự chạy slider
    setInterval(function () {

        container.scrollLeft += container.clientWidth;

        if (container.scrollLeft >= container.scrollWidth - container.clientWidth) {
            container.scrollLeft = 0;
        }

    }, 4000);

});