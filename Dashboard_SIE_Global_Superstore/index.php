<?php
require 'config.php';

// Ambil tahun yang dipilih (default tahun saat ini atau semua tahun)
$selected_year = isset($_GET['year']) ? $_GET['year'] : 'all';

// Query untuk mendapatkan daftar tahun yang tersedia
$years_query = "SELECT DISTINCT YEAR(order_date) as year FROM global_superstore ORDER BY year DESC";
$available_years = $pdo->query($years_query)->fetchAll();

// Kondisi WHERE berdasarkan tahun yang dipilih
$year_condition = $selected_year !== 'all' ? "WHERE YEAR(order_date) = " . intval($selected_year) : "";

// Ambil statistik ringkasan
$stats = [
    'total_sales' => $pdo->query("SELECT SUM(sales) as total FROM global_superstore $year_condition")->fetch()['total'],
    'total_profit' => $pdo->query("SELECT SUM(profit) as total FROM global_superstore $year_condition")->fetch()['total'],
    'total_orders' => $pdo->query("SELECT COUNT(DISTINCT order_id) as total FROM global_superstore $year_condition")->fetch()['total'],
    'total_customers' => $pdo->query("SELECT COUNT(DISTINCT customer_name) as total FROM global_superstore $year_condition")->fetch()['total']
];

// Data untuk chart kategori
$categories = $pdo->query("SELECT category, SUM(sales) as total_sales FROM global_superstore $year_condition GROUP BY category ORDER BY total_sales DESC")->fetchAll();

// Data region untuk sales dan profit
$regions_sales = $pdo->query("SELECT region, SUM(sales) as total_sales FROM global_superstore $year_condition GROUP BY region ORDER BY total_sales DESC")->fetchAll();

$regions_profit = $pdo->query("SELECT region, SUM(profit) as total_profit FROM global_superstore $year_condition GROUP BY region ORDER BY total_profit DESC")->fetchAll();

// Data region untuk tabel
$regions = $pdo->query("SELECT region, SUM(sales) as total_sales, SUM(profit) as total_profit FROM global_superstore $year_condition GROUP BY region ORDER BY total_sales DESC")->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Eksekutif - Global Superstore</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
  
  <!-- Sidebar Mobile Toggle -->
  <div class="lg:hidden fixed top-4 left-4 z-50">
    <button id="menuToggle" class="bg-purple-600 text-white p-3 rounded-lg shadow-lg">
      <i class="fas fa-bars"></i>
    </button>
  </div>

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40">
    <div class="p-6 border-b border-slate-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
          <i class="fas fa-store text-white text-xl"></i>
        </div>
        <div>
          <h1 class="text-lg font-bold text-slate-800">Global Store</h1>
          <p class="text-xs text-slate-500">Executive Dashboard</p>
        </div>
      </div>
    </div>

    <nav class="p-4 space-y-2">
      <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-lg font-medium transition-all">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard</span>
      </a>
      <a href="menampilkan.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
        <i class="fas fa-table"></i>
        <span>Data Transaksi</span>
      </a>
      <a href="drilldown.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
        <i class="fas fa-layer-group"></i>
        <span>Drilldown Analysis</span>
      </a>
      <a href="whatif.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
        <i class="fas fa-calculator"></i>
        <span>What-If Analysis</span>
      </a>
    </nav>

    <div class="absolute bottom-0 w-full p-4 border-t border-slate-200">
      <div class="bg-purple-50 p-3 rounded-lg">
        <p class="text-xs text-slate-600 mb-1">Data Source</p>
        <p class="text-sm font-medium text-purple-700">MySQL Database</p>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="lg:ml-64 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-slate-200 px-6 lg:px-8 py-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-800">Dashboard Eksekutif</h2>
          <p class="text-sm text-slate-500 mt-1">
            <i class="far fa-calendar-alt mr-1"></i>
            <?= date('l, d F Y') ?>
          </p>
        </div>
        <div class="flex items-center gap-3">
          <select id="yearFilter" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
            <option value="all" <?= $selected_year === 'all' ? 'selected' : '' ?>>Semua Tahun</option>
            <?php foreach ($available_years as $year): ?>
              <option value="<?= $year['year'] ?>" <?= $selected_year == $year['year'] ? 'selected' : '' ?>>
                Tahun <?= $year['year'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </header>

    <!-- Stats Cards -->
    <div class="p-6 lg:p-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
        <!-- Total Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
          </div>
          <h3 class="text-slate-600 text-sm font-medium mb-1">Total Sales</h3>
          <p class="text-2xl font-bold text-slate-800">$<?= number_format($stats['total_sales'], 0) ?></p>
        </div>

        <!-- Total Profit -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
          </div>
          <h3 class="text-slate-600 text-sm font-medium mb-1">Total Profit</h3>
          <p class="text-2xl font-bold text-slate-800">$<?= number_format($stats['total_profit'], 0) ?></p>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-shopping-cart text-amber-600 text-xl"></i>
            </div>
          </div>
          <h3 class="text-slate-600 text-sm font-medium mb-1">Total Orders</h3>
          <p class="text-2xl font-bold text-slate-800"><?= number_format($stats['total_orders'], 0) ?></p>
        </div>

        <!-- Total Customers -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
          </div>
          <h3 class="text-slate-600 text-sm font-medium mb-1">Total Customers</h3>
          <p class="text-2xl font-bold text-slate-800"><?= number_format($stats['total_customers'], 0) ?></p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales by Category -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100">
          <h3 class="text-lg font-semibold text-slate-800 mb-4">Sales by Category</h3>
          <canvas id="categoryChart" class="max-h-64"></canvas>
        </div>

        <!-- Sales by Region -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Sales by Region</h3>
            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
              <?= $selected_year === 'all' ? 'All Years' : 'Year ' . $selected_year ?>
            </span>
          </div>
          <canvas id="regionSalesChart" class="max-h-64"></canvas>
        </div>

        <!-- Profit by Region -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 lg:col-span-2">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Profit by Region</h3>
            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
              <?= $selected_year === 'all' ? 'All Years' : 'Year ' . $selected_year ?>
            </span>
          </div>
          <canvas id="regionProfitChart" class="max-h-64"></canvas>
        </div>
      </div>

      <!-- Regional Performance Table -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-200">
          <h3 class="text-lg font-semibold text-slate-800">Regional Performance</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Region</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Total Sales</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Total Profit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Profit Margin</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <?php foreach ($regions as $region): 
                $margin = ($region['total_profit'] / $region['total_sales']) * 100;
              ?>
              <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-map-marker-alt text-purple-600 text-sm"></i>
                    </div>
                    <span class="font-medium text-slate-800"><?= htmlspecialchars($region['region']) ?></span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-slate-700 font-medium">
                  $<?= number_format($region['total_sales'], 2) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-slate-700 font-medium">
                  $<?= number_format($region['total_profit'], 2) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="px-3 py-1 rounded-full text-sm font-medium <?= $margin > 15 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                    <?= number_format($margin, 1) ?>%
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    menuToggle?.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (window.innerWidth < 1024) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          sidebar.classList.add('-translate-x-full');
        }
      }
    });

    // Year filter change handler
    document.getElementById('yearFilter').addEventListener('change', function() {
      const year = this.value;
      window.location.href = '?year=' + year;
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode(array_column($categories, 'category')) ?>,
        datasets: [{
          data: <?= json_encode(array_column($categories, 'total_sales')) ?>,
          backgroundColor: ['#4887ecff', '#10b981', '#f59e0b'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: { size: 12 }
            }
          }
        }
      }
    });

    // Region Sales Chart
    const regionSalesCtx = document.getElementById('regionSalesChart').getContext('2d');
    new Chart(regionSalesCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($regions_sales, 'region')) ?>,
        datasets: [{
          label: 'Sales',
          data: <?= json_encode(array_column($regions_sales, 'total_sales')) ?>,
          backgroundColor: '#4887ecff',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '$' + value.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Region Profit Chart
    const regionProfitCtx = document.getElementById('regionProfitChart').getContext('2d');
    new Chart(regionProfitCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($regions_profit, 'region')) ?>,
        datasets: [{
          label: 'Profit',
          data: <?= json_encode(array_column($regions_profit, 'total_profit')) ?>,
          backgroundColor: '#10b981',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Profit: $' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '$' + value.toLocaleString();
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>
