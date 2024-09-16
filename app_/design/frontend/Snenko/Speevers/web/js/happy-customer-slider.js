
document.addEventListener('DOMContentLoaded', function () {
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');
    const testimonials = document.querySelector('.testimonials');
    const slides = document.querySelectorAll('.testimonial'); // Get all slides
    const totalSlides = slides.length; // Total number of slides
    let index = 1; // Start with the second slide (index 1)

    function showSlide(slideIndex) {
        // Calculate offset based on the number of slides
        if (slideIndex >= totalSlides) index = 0;
        else if (slideIndex < 0) index = totalSlides - 1;
        else index = slideIndex;

        // Remove 'central' class from the currently visible slide
        slides.forEach(slide => {
            slide.classList.remove('central');
            slide.style.opacity = '0.6'; // Set opacity for non-active slides
        });

        // Calculate percentage offset for sliding effect
        const offset = -(index * (100 / totalSlides));
        testimonials.style.transform = `translateX(${(100 / totalSlides) + offset}%)`;

        // Add 'central' class to the new slide
        slides[index].classList.add('central');
        slides[index].style.opacity = '1'; // Set opacity for the active slide
    }

    prevButton.addEventListener('click', function () {
        showSlide(index - 1);
    });

    nextButton.addEventListener('click', function () {
        showSlide(index + 1);
    });

    showSlide(index); // Show the second slide initially
});

