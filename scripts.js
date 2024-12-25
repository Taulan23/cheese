document.addEventListener('DOMContentLoaded', function() {
    // Инициализация слайдера изображений
    const images = ['cheese1.jpg', 'cheese2.jpg', 'cheese3.jpg'];
    let currentImage = 0;
    const imageElement = document.getElementById('cheese-image');

    // Предзагрузка изображений
    const preloadImages = images.map(src => {
        const img = new Image();
        img.src = 'images/' + src;
        return img;
    });

    // Функция смены изображения
    function changeImage() {
        if (imageElement) {
            currentImage = (currentImage + 1) % images.length;
            // Меняем только src изображения, без перезагрузки страницы
            imageElement.src = 'images/' + images[currentImage];
        }
    }

    // Смена изображения каждые 3 секунды
    if (imageElement) {
        setInterval(changeImage, 3000);
    }

    // Обновление таймера
    function updateTimer() {
        const timer = document.getElementById('timer');
        if (timer) {
            const now = new Date();
            const nextSale = new Date();
            nextSale.setHours(24, 0, 0, 0);
            
            const diff = nextSale - now;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            timer.textContent = `${hours}:${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }
    }

    // Обновление часов
    function updateClock() {
        const clock = document.getElementById('clock');
        if (clock) {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();
            
            clock.textContent = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }
    }

    // Обновление таймера и часов каждую секунду
    setInterval(updateTimer, 1000);
    setInterval(updateClock, 1000);
    updateTimer();
    updateClock();
});
