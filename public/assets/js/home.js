// Danh sách ảnh nền (Nên chọn ảnh bạn đang dẫn khách, hoặc phong cảnh đẹp nơi bạn làm việc)
const images = [
    'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80', // Đà Nẵng
    'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80', // Núi non
    'https://images.unsplash.com/photo-1528127269322-539801943592?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80'  // Hạ Long
];

const heroSection = document.getElementById('hero-slider');
const dotsContainer = document.getElementById('dots-container');
let currentIndex = 0;
const intervalTime = 6000; // 6 giây đổi ảnh một lần
let slideInterval;

function initSlider() {
    heroSection.style.backgroundImage = `url('${images[0]}')`;

    images.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateSlider();
            resetInterval();
        });
        dotsContainer.appendChild(dot);
    });

    startSlide();
}

function updateSlider() {
    heroSection.style.backgroundImage = `url('${images[currentIndex]}')`;
    const dots = document.querySelectorAll('.dot');
    dots.forEach(dot => dot.classList.remove('active'));
    dots[currentIndex].classList.add('active');
}

function nextSlide() {
    currentIndex++;
    if (currentIndex >= images.length) {
        currentIndex = 0;
    }
    updateSlider();
}

function startSlide() {
    slideInterval = setInterval(nextSlide, intervalTime);
}

function resetInterval() {
    clearInterval(slideInterval);
    startSlide();
}

document.addEventListener('DOMContentLoaded', initSlider);