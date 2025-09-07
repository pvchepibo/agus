<?php
// File ini akan menjadi sidebar untuk semua halaman admin
?>
<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fas fa-book-open fa-2x me-2"></i>
        <span class="fs-4">PerpusWeb</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white">
                <i class="fas fa-tachometer-alt fa-fw me-2"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="buku.php" class="nav-link text-white">
                <i class="fas fa-book fa-fw me-2"></i>Data Buku
            </a>
        </li>
        <li>
            <a href="kategori.php" class="nav-link text-white">
                <i class="fas fa-tags fa-fw me-2"></i>Data Kategori
            </a>
        </li>
        <li>
            <a href="anggota.php" class="nav-link text-white">
                <i class="fas fa-users fa-fw me-2"></i>Data Anggota
            </a>
        </li>
        <li>
            <a href="transaksi.php" class="nav-link text-white">
                <i class="fas fa-exchange-alt fa-fw me-2"></i>Transaksi
            </a>
        </li>
        <li>
            <a href="laporan.php" class="nav-link text-white">
                <i class="fas fa-file-alt fa-fw me-2"></i>Laporan
            </a>
        </li>
        <!-- === LINK BARU DITAMBAHKAN DI SINI === -->
        <li>
            <a href="pengumuman.php" class="nav-link text-white">
                <i class="fas fa-bullhorn fa-fw me-2"></i>Pengumuman
            </a>
        </li>
        <!-- ==================================== -->
        <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin'): ?>
        <li>
            <a href="pengguna.php" class="nav-link text-white">
                <i class="fas fa-user-cog fa-fw me-2"></i>Kelola Pengguna
            </a>
        </li>
        <li>
            <a href="pengaturan.php" class="nav-link text-white">
                <i class="fas fa-cogs fa-fw me-2"></i>Pengaturan
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://placehold.co/32x32/EFEFEF/333333?text=<?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="#">Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
    </div>
</div>