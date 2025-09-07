// js/script.js

document.addEventListener('DOMContentLoaded', function() {

    // --- FUNGSI HITUNG MUNDUR ---
    const startDate = new Date(2024, 8, 6).getTime();

    const countdownInterval = setInterval(function() {
        const now = new Date().getTime();
        const distance = now - startDate;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('days').innerText = days;
        document.getElementById('hours').innerText = hours;
        document.getElementById('minutes').innerText = minutes;
        document.getElementById('seconds').innerText = seconds;

    }, 1000);


    // --- FUNGSI GALERI FOTO ---
    const images = [
        'foto1.jpg',
        'foto2.jpg',
        'foto3.jpg',
        'foto4.jpg'
        // Tambahkan nama file foto lainnya di sini
    ];

    let currentIndex = 0;
    const galleryImage = document.getElementById('gallery-image');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const imageCounter = document.getElementById('image-counter');

    function updateGallery() {
        galleryImage.src = `images/${images[currentIndex]}`;
        imageCounter.textContent = `${currentIndex + 1} / ${images.length}`;
    }

    nextBtn.addEventListener('click', function() {
        currentIndex++;
        if (currentIndex >= images.length) {
            currentIndex = 0;
        }
        updateGallery();
    });

    prevBtn.addEventListener('click', function() {
        currentIndex--;
        if (currentIndex < 0) {
            currentIndex = images.length - 1;
        }
        updateGallery();
    });
    
    updateGallery();


    // --- FUNGSI MUSIK LATAR & EFEK HATI ---
    const music = document.getElementById('background-music');
    const playButton = document.getElementById('play-music-button');
    let isPlaying = false;

    // DIUBAH: Menambahkan parameter event (e) untuk mendapatkan posisi klik
    playButton.addEventListener('click', function(e) { 
        if (isPlaying) {
            music.pause();
            playButton.textContent = "🎵";
        } else {
            music.play();
            playButton.textContent = "⏸️";
        }
        isPlaying = !isPlaying;

        // BARU: Panggil fungsi untuk membuat efek hati
        createHeartEffect(e);
    });

    // BARU: Fungsi untuk membuat efek hati terbang
    function createHeartEffect(e) {
        const buttonRect = playButton.getBoundingClientRect();
        // Buat banyak hati sekaligus dalam satu kali klik
        for (let i = 0; i < 10; i++) {
            const heart = document.createElement('div');
            heart.className = 'heart-effect';
            heart.innerHTML = '❤️';
            document.body.appendChild(heart);

            // Posisi awal hati di sekitar tombol
            const startX = buttonRect.left + buttonRect.width / 2;
            const startY = buttonRect.top + buttonRect.height / 2;
            
            // Gerakan acak agar menyebar
            const randomX = Math.random() * 100 - 50; // Menyebar ke kiri & kanan
            const randomY = Math.random() * 50 - 25;  // Sedikit sebaran vertikal

            heart.style.left = `${startX + randomX}px`;
            heart.style.top = `${startY + randomY}px`;
            
            // Hapus elemen hati setelah animasi selesai agar tidak menumpuk
            setTimeout(() => {
                heart.remove();
            }, 2000); // 2000ms = 2 detik, sesuai durasi animasi
        }
    }
});