<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-donate text-primary me-2"></i> Total Donasi</h5>
                    <h2 class="display-6 fw-bold mb-0">Rp <?= number_format($donationStats['total_amount'] ?? 0, 0, ',', '.') ?></h2>
                    <p class="text-muted"><?= number_format($donationStats['total_donations'] ?? 0, 0, ',', '.') ?> transaksi</p>
                    <div class="mt-3">
                        <span class="badge bg-<?= ($donationStats['growth_percentage'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                            <i class="fas fa-<?= ($donationStats['growth_percentage'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i> 
                            <?= abs($donationStats['growth_percentage'] ?? 0) ?>%
                        </span>
                        <span class="text-muted small ms-1">vs bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-hand-holding-heart text-success me-2"></i> Kampanye Aktif</h5>
                    <h2 class="display-6 fw-bold mb-0"><?= number_format($activeCampaigns['count'] ?? 0, 0, ',', '.') ?></h2>
                    <p class="text-muted">Dari total <?= number_format($activeCampaigns['total'] ?? 0, 0, ',', '.') ?> kampanye</p>
                    <div class="progress mt-3" style="height: 5px;">
                        <?php $percentage = ($activeCampaigns['count'] ?? 0) > 0 && ($activeCampaigns['total'] ?? 0) > 0 
                            ? (($activeCampaigns['count'] / $activeCampaigns['total']) * 100) : 0; ?>
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%;" 
                            aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-users text-info me-2"></i> Total Donatur</h5>
                    <h2 class="display-6 fw-bold mb-0"><?= number_format($donationStats['total_donors'] ?? 0, 0, ',', '.') ?></h2>
                    <p class="text-muted"><?= number_format($donationStats['new_donors'] ?? 0, 0, ',', '.') ?> donatur baru bulan ini</p>
                    <div class="mt-3">
                        <span class="badge bg-<?= ($donationStats['donor_growth'] ?? 0) >= 0 ? 'info' : 'warning' ?>">
                            <i class="fas fa-<?= ($donationStats['donor_growth'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i> 
                            <?= abs($donationStats['donor_growth'] ?? 0) ?>%
                        </span>
                        <span class="text-muted small ms-1">vs bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-chart-line text-warning me-2"></i> Rata-Rata Donasi</h5>
                    <h2 class="display-6 fw-bold mb-0">Rp <?= number_format($donationStats['avg_donation'] ?? 0, 0, ',', '.') ?></h2>
                    <p class="text-muted">Per transaksi</p>
                    <div class="mt-3">
                        <span class="badge bg-<?= ($donationStats['avg_growth'] ?? 0) >= 0 ? 'warning' : 'secondary' ?>">
                            <i class="fas fa-<?= ($donationStats['avg_growth'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i> 
                            <?= abs($donationStats['avg_growth'] ?? 0) ?>%
                        </span>
                        <span class="text-muted small ms-1">vs bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reports & Download Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Laporan & Export Data</h5>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#reportModal">
                                <i class="fas fa-filter me-1"></i> Filter Laporan
                            </button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/reports/donations?type=excel" class="card h-100 border-0 shadow-sm text-decoration-none">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-file-excel text-success fa-2x"></i>
                                    </div>
                                    <h5>Laporan Donasi</h5>
                                    <p class="text-muted small mb-0">Export data donasi (Excel)</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/reports/campaigns?type=excel" class="card h-100 border-0 shadow-sm text-decoration-none">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-file-excel text-primary fa-2x"></i>
                                    </div>
                                    <h5>Laporan Kampanye</h5>
                                    <p class="text-muted small mb-0">Export data kampanye (Excel)</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/reports/donations?type=pdf" class="card h-100 border-0 shadow-sm text-decoration-none">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-file-pdf text-danger fa-2x"></i>
                                    </div>
                                    <h5>Laporan Donasi PDF</h5>
                                    <p class="text-muted small mb-0">Export data donasi (PDF)</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/reports/summary" class="card h-100 border-0 shadow-sm text-decoration-none">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-chart-pie text-info fa-2x"></i>
                                    </div>
                                    <h5>Ringkasan Statistik</h5>
                                    <p class="text-muted small mb-0">Laporan lengkap dalam satu file</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Grafik Donasi</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active period-btn" data-period="weekly">Mingguan</button>
                        <button type="button" class="btn btn-outline-primary period-btn" data-period="monthly">Bulanan</button>
                        <button type="button" class="btn btn-outline-primary period-btn" data-period="yearly">Tahunan</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($donationStats['chart_data'])): ?>
                    <div class="text-center p-5">
                        <i class="fas fa-chart-bar text-muted fa-3x mb-3"></i>
                        <h5>Belum ada data</h5>
                        <p class="text-muted">Data akan ditampilkan ketika donasi mulai masuk</p>
                    </div>
                    <?php else: ?>
                        <canvas id="donationChart" height="300"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Metode Pembayaran</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($donationStats['payment_methods'])): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-credit-card text-muted fa-3x mb-3"></i>
                            <h5>Belum ada data</h5>
                            <p class="text-muted">Data akan ditampilkan ketika donasi mulai masuk</p>
                        </div>
                    <?php else: ?>
                        <canvas id="paymentMethodChart" height="260"></canvas>
                        <div class="mt-3">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($donationStats['payment_methods'] as $method): ?>
                                            <tr>
                                                <td>
                                                    <span class="d-inline-block" style="width: 10px; height: 10px; background-color: <?= $method['color'] ?>; margin-right: 5px;"></span>
                                                    <?= $method['label'] ?>
                                                </td>
                                                <td class="text-end"><?= number_format($method['count'], 0, ',', '.') ?></td>
                                                <td class="text-end"><?= number_format($method['percentage'], 1) ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Donasi Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentDonations)): ?>
                    <div class="text-center p-5">
                        <i class="fas fa-donate text-muted fa-3x mb-3"></i>
                        <h5>Belum ada donasi</h5>
                        <p class="text-muted">Donasi terbaru akan ditampilkan di sini</p>
                    </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Donatur</th>
                                        <th>Kampanye</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentDonations as $donation): ?>
                                        <tr>
                                            <td>
                                                <?= $donation['is_anonymous'] ? 'Anonim' : $donation['name'] ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/admin/campaigns/edit/<?= $donation['campaign_id'] ?>" class="text-decoration-none">
                                                    <?= $donation['campaign_title'] ?>
                                                </a>
                                            </td>
                                            <td>Rp <?= number_format($donation['amount'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                $statusText = '';
                                                
                                                switch($donation['status']) {
                                                    case 'pending':
                                                        $statusClass = 'warning';
                                                        $statusText = 'Menunggu';
                                                        break;
                                                    case 'success':
                                                        $statusClass = 'success';
                                                        $statusText = 'Berhasil';
                                                        break;
                                                    case 'failed':
                                                        $statusClass = 'danger';
                                                        $statusText = 'Gagal';
                                                        break;
                                                    default:
                                                        $statusClass = 'secondary';
                                                        $statusText = ucfirst($donation['status']);
                                                }
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($donation['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/admin/donations" class="btn btn-sm btn-outline-primary">Lihat Semua Donasi</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Kampanye Teratas</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activeCampaigns['list'])): ?>
                    <div class="text-center p-5">
                        <i class="fas fa-hand-holding-heart text-muted fa-3x mb-3"></i>
                        <h5>Belum ada kampanye</h5>
                        <p class="text-muted">Kampanye akan ditampilkan di sini saat dibuat</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($activeCampaigns['list'] as $campaign): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" 
                                    alt="<?= $campaign['title'] ?>" class="rounded" width="60" height="60" style="object-fit: cover;">
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="<?= BASE_URL ?>/admin/campaigns/edit/<?= $campaign['id'] ?>" class="text-decoration-none">
                                            <?= $campaign['title'] ?>
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="progress mb-1" style="height: 5px; width: 100px;">
                                                <?php 
                                                $percentage = min(($campaign['current_amount'] / $campaign['goal_amount']) * 100, 100);
                                                ?>
                                                <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <small class="text-muted">
                                                <?= number_format($percentage, 0) ?>% dari Rp <?= number_format($campaign['goal_amount'], 0, ',', '.') ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?= $campaign['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= $campaign['status'] === 'active' ? 'Aktif' : ucfirst($campaign['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-sm btn-outline-primary">Lihat Semua Kampanye</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Filter Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Filter Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL ?>/admin/reports/generate" method="post" id="reportForm">
                    <div class="mb-3">
                        <label for="reportType" class="form-label">Jenis Laporan</label>
                        <select class="form-select" id="reportType" name="report_type" required>
                            <option value="">Pilih Jenis Laporan</option>
                            <option value="donations">Laporan Donasi</option>
                            <option value="campaigns">Laporan Kampanye</option>
                            <option value="donors">Laporan Donatur</option>
                            <option value="summary">Laporan Ringkasan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dateRange" class="form-label">Rentang Waktu</label>
                        <select class="form-select" id="dateRange" name="date_range">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="last7days">7 Hari Terakhir</option>
                            <option value="last30days" selected>30 Hari Terakhir</option>
                            <option value="thisMonth">Bulan Ini</option>
                            <option value="lastMonth">Bulan Lalu</option>
                            <option value="thisYear">Tahun Ini</option>
                            <option value="custom">Kustom</option>
                        </select>
                    </div>
                    <div class="row mb-3 date-range-custom d-none">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="startDate" name="start_date">
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="endDate" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fileFormat" class="form-label">Format File</label>
                        <select class="form-select" id="fileFormat" name="file_format" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('reportForm').submit()">Generate Laporan</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<!-- Charts Initialization -->
<script>
<?php if (!empty($donationStats['chart_data'])): ?>
// Donation Chart
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data
    const chartData = <?= json_encode($donationStats['chart_data']) ?>;
    const ctx = document.getElementById('donationChart').getContext('2d');
    
    // Create chart
    const donationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Jumlah Donasi (Rp)',
                    data: chartData.amounts,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Jumlah Transaksi',
                    data: chartData.counts,
                    borderColor: '#198754',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'transactions'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label.includes('Donasi')) {
                                return label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            } else {
                                return label + ': ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                legend: {
                    position: 'top',
                    align: 'end'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                transactions: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        drawBorder: false
                    }
                }
            }
        }
    });
    
    // Period buttons functionality
    const periodButtons = document.querySelectorAll('.period-btn');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            periodButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update chart data based on period
            const period = this.getAttribute('data-period');
            // This would be an AJAX call in a real implementation
            // Here we're just simulating period changes
            updateChartPeriod(donationChart, period);
        });
    });
});

// Update chart based on period (placeholder function)
function updateChartPeriod(chart, period) {
    // This would be replaced with real AJAX call and data update
    console.log(`Updating chart to ${period} period`);
    
    // Simulate data update
    const periodsData = {
        'weekly': {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            amounts: [500000, 750000, 300000, 900000, 650000, 1200000, 800000],
            counts: [5, 7, 3, 8, 6, 11, 7]
        },
        'monthly': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            amounts: [5000000, 7500000, 3000000, 9000000, 6500000, 12000000, 8000000, 9500000, 11000000, 8500000, 13000000, 15000000],
            counts: [50, 70, 35, 85, 60, 110, 75, 90, 105, 80, 125, 140]
        },
        'yearly': {
            labels: ['2020', '2021', '2022', '2023', '2024'],
            amounts: [50000000, 75000000, 90000000, 120000000, 95000000],
            counts: [500, 700, 850, 1100, 900]
        }
    };
    
    if (periodsData[period]) {
        chart.data.labels = periodsData[period].labels;
        chart.data.datasets[0].data = periodsData[period].amounts;
        chart.data.datasets[1].data = periodsData[period].counts;
        chart.update();
    }
}
<?php endif; ?>

<?php if (!empty($donationStats['payment_methods'])): ?>
// Payment Method Chart
document.addEventListener('DOMContentLoaded', function() {
    const paymentData = <?= json_encode($donationStats['payment_methods']) ?>;
    const ctxPie = document.getElementById('paymentMethodChart').getContext('2d');
    
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: paymentData.map(item => item.label),
            datasets: [{
                data: paymentData.map(item => item.percentage),
                backgroundColor: paymentData.map(item => item.color),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.formattedValue}%`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
});
<?php endif; ?>

// Date Range Functionality
document.addEventListener('DOMContentLoaded', function() {
    const dateRange = document.getElementById('dateRange');
    const customDateFields = document.querySelector('.date-range-custom');
    
    dateRange.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateFields.classList.remove('d-none');
        } else {
            customDateFields.classList.add('d-none');
        }
    });
});
</script>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?>
