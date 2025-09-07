<?php
// File ini akan menjadi footer untuk semua halaman publik
?>
<footer class="site-footer mt-auto bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row">
            <!-- Kolom 1: Tentang & Sosial Media -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-book-open me-2"></i>PerpusWeb</h5>
                <p class="text-white-50">Sebuah platform digital untuk menjelajahi koleksi Perpustakaan Daerah Manokwari dengan mudah dan nyaman.</p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>

            <!-- Kolom 2: Jam Pelayanan -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Jam Pelayanan</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="d-flex justify-content-between"><span>Senin - Jumat</span> <span>08:00 - 16:00</span></li>
                    <li class="d-flex justify-content-between"><span>Sabtu</span> <span>09:00 - 13:00</span></li>
                    <li class="d-flex justify-content-between"><span>Minggu</span> <span>Tutup</span></li>
                </ul>
            </div>

            <!-- Kolom 3: Kontak -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Kontak Kami</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Jln. Pahlawan, Sanggeng, Manokwari Barat</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> disarpuspb@gmail.com</li>
                </ul>
            </div>

            <!-- Kolom 4: Peta Lokasi (SUDAH DIPERBARUI) -->
            <div class="col-lg-3 col-md-6 mb-4">
                 <h5 class="fw-bold mb-3">Lokasi Kami</h5>
                 <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.374236598885!2d134.0689092!3d-0.8548650000000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d540aedbc41f537%3A0x476a52b18d72d8d2!2sPerpustakaan%20Daerah%20Manokwari!5e0!3m2!1sid!2sid!4v1757249499360!5m2!1sid!2sid" 
                        width="100%" 
                        height="150" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                 </div>
            </div>
        </div>
        <hr class="bg-white-50">
        <div class="text-center text-white-50">
            &copy; <?php echo date("Y"); ?> Perpustakaan Daerah Manokwari. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Custom JS -->
<script src="assets/js/script.js"></script>

<script>
    // Inisialisasi AOS (Animasi saat scroll)
    AOS.init({
        duration: 800,
        once: true
    });

    // --- Script untuk Grafik Pengguna Online ---
    const ctx = document.getElementById('grafikPenggunaOnline');
    if (ctx) {
        const grafikPengguna = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [ new Date().toLocaleTimeString() ],
                datasets: [{
                    label: 'Pengguna Online',
                    data: [0],
                    borderColor: 'rgba(13, 110, 253, 1)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Fungsi untuk update grafik setiap 5 detik
        setInterval(async () => {
            try {
                const response = await fetch('api_online_users.php');
                const data = await response.json();
                
                // Tambah data baru ke grafik
                grafikPengguna.data.labels.push(new Date().toLocaleTimeString());
                grafikPengguna.data.datasets[0].data.push(data.online_users);

                // Hapus data lama (jaga agar tidak terlalu banyak titik)
                if (grafikPengguna.data.labels.length > 10) {
                    grafikPengguna.data.labels.shift();
                    grafikPengguna.data.datasets[0].data.shift();
                }
                
                grafikPengguna.update();
            } catch (error) {
                console.error("Gagal mengambil data pengguna online:", error);
            }
        }, 5000);
    }
</script>
</body>
</html>