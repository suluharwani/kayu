<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistem Manajemen Stock Kayu'; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Add this in the <head> section or before your script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center">Manajemen Kayu</h4>
                    <hr class="bg-light">
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link <?= uri_string() == 'dashboard' ? 'active' : '' ?>" href="/dashboard">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <div class="dropdown-divider"></div>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-database mr-2"></i>Master Data
                        </a>
                        <div class="dropdown-menu bg-dark">
                            <a class="dropdown-item text-light <?= uri_string() == 'gudang' ? 'active' : '' ?>" href="/gudang">Gudang</a>
                            <a class="dropdown-item text-light <?= uri_string() == 'jenis-kayu' ? 'active' : '' ?>" href="/jenis-kayu">Jenis Kayu</a>
                            <a class="dropdown-item text-light <?= uri_string() == 'kayu' ? 'active' : '' ?>" href="/kayu">Data Kayu</a>
                        </div>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-exchange-alt mr-2"></i>Transaksi
                        </a>
                        <div class="dropdown-menu bg-dark">
                            <a class="dropdown-item text-light <?= uri_string() == 'transaksi/masuk' ? 'active' : '' ?>" href="/transaksi/masuk">Masuk Gudang</a>
                            <a class="dropdown-item text-light <?= uri_string() == 'transaksi/keluar' ? 'active' : '' ?>" href="/transaksi/keluar">Keluar Gudang</a>
                            <a class="dropdown-item text-light <?= uri_string() == 'transaksi/mutasi' ? 'active' : '' ?>" href="/transaksi/mutasi">Mutasi Gudang</a>
                            <a class="dropdown-item text-light <?= uri_string() == 'transaksi' ? 'active' : '' ?>" href="/transaksi">Daftar Transaksi</a>
                        </div>
                    </li>
                    
                    <a class="nav-link <?= uri_string() == 'laporan' ? 'active' : '' ?>" href="/laporan">
                        <i class="fas fa-file-alt mr-2"></i>Laporan
                    </a>
                    
                    <div class="dropdown-divider"></div>
                    
                    <a class="nav-link <?= uri_string() == 'pengguna' ? 'active' : '' ?>" href="/pengguna">
                        <i class="fas fa-users mr-2"></i>Pengguna
                    </a>
                    
                    <div class="dropdown-divider"></div>
                    
                    <a class="nav-link" href="/logout">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <?php 
                        $uri = service('uri');
                        $segments = $uri->getSegments();
                        $last = end($segments);
                        foreach($segments as $segment):
                            if($segment == $last): ?>
                                <li class="breadcrumb-item active"><?= ucfirst($segment); ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="/<?= $segment; ?>"><?= ucfirst($segment); ?></a></li>
                            <?php endif;
                        endforeach; ?>
                    </ol>
                </nav>
                
                <?php if(session()->getFlashdata('message')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('message'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error'); ?>
                    </div>
                <?php endif; ?>
                
                <?= $this->renderSection('content'); ?>
            </div>
        </div>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?= $this->renderSection('scripts'); ?>
</body>
</html>