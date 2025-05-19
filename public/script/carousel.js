let currentIndex = 0;
const images = document.querySelectorAll(".carousel img");
const totalImages = images.length;
const intervalTime = 15000; // 15 secondes

function showImage(index) {
    images.forEach((img, i) => {
        img.classList.toggle("active", i === index);
    });
}

function nextImage() {
    currentIndex = (currentIndex + 1) % totalImages;
    showImage(currentIndex);
}

function prevImage() {
    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
    showImage(currentIndex);
}

let autoSlide = setInterval(nextImage, intervalTime);

document.querySelector(".next").addEventListener("click", () => {
    nextImage();
    resetInterval();
});
document.querySelector(".prev").addEventListener("click", () => {
    prevImage();
    resetInterval();
});

function resetInterval() {
    clearInterval(autoSlide);
    autoSlide = setInterval(nextImage, intervalTime);
}

showImage(currentIndex);
