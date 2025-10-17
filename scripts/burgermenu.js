const burger = document.querySelector('.burger');
const navBar = document.querySelector('.navBar');

burger.addEventListener('click', function () {
    burger.classList.toggle('active');
    navBar.classList.toggle('active');
    if ( document.body.style.overflow === 'hidden') {
        document.body.style.overflow = 'auto';
    } else {
        document.body.style.overflow = 'hidden'
    }

});

const links = document.querySelectorAll('.navBar a');
links.forEach(function (link) {
    link.addEventListener('click', function () {
        burger.classList.remove('active');
        navBar.classList.remove('active');
        document.body.style.overflow = 'auto';
    });
});
