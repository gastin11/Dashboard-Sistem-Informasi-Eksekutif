<?php
require 'config.php';

// Fungsi untuk mendapatkan data berdasarkan level drilldown
function getDrilldownData($pdo, $level, $metric, $filters = []) {
    $select = '';
    $groupBy = '';
    $where = [];
    $params = [];
    
    // Tentukan kolom berdasarkan level
    switch($level) {
        case 'region':
            $select = 'region';
            $groupBy = 'region';
            break;
        case 'country':
            $select = 'country';
            $groupBy = 'country';
            if (isset($filters['region'])) {
                $where[] = 'region = :region';
                $params[':region'] = $filters['region'];
            }
            break;
        case 'state':
            $select = 'state';
            $groupBy = 'state';
            if (isset($filters['region'])) {
                $where[] = 'region = :region';
                $params[':region'] = $filters['region'];
            }
            if (isset($filters['country'])) {
                $where[] = 'country = :country';
                $params[':country'] = $filters['country'];
            }
            break;
        case 'city':
            $select = 'city';
            $groupBy = 'city';
            if (isset($filters['region'])) {
                $where[] = 'region = :region';
                $params[':region'] = $filters['region'];
            }
            if (isset($filters['country'])) {
                $where[] = 'country = :country';
                $params[':country'] = $filters['country'];
            }
            if (isset($filters['state'])) {
                $where[] = 'state = :state';
                $params[':state'] = $filters['state'];
            }
            break;
    }
    
    // Tentukan metric
    $metricSQL = '';
    if ($metric === 'profit') {
        $metricSQL = 'SUM(profit) as value';
    } elseif ($metric === 'sales') {
        $metricSQL = 'SUM(sales) as value';
    } elseif ($metric === 'ratio') {
        $metricSQL = 'SUM(profit) as profit, SUM(sales) as sales';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT $select as label, $metricSQL 
            FROM global_superstore 
            $whereClause 
            GROUP BY $groupBy 
            ORDER BY value DESC 
            LIMIT 15";
    
    if ($metric === 'ratio') {
        $sql = "SELECT $select as label, SUM(profit) as profit, SUM(sales) as sales 
                FROM global_superstore 
                $whereClause 
                GROUP BY $groupBy 
                ORDER BY (SUM(profit) / SUM(sales)) DESC 
                LIMIT 15";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    if ($metric === 'ratio') {
        foreach ($results as &$row) {
            $row['value'] = $row['sales'] > 0 ? ($row['profit'] / $row['sales']) * 100 : 0;
        }
    }
    
    return $results;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Drilldown Analysis - Global Superstore</title>
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
      <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard</span>
      </a>
      <a href="menampilkan.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
        <i class="fas fa-table"></i>
        <span>Data Transaksi</span>
      </a>
      <a href="drilldown.php" class="flex items-center gap-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-lg font-medium transition-all">
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
          <h2 class="text-2xl font-bold text-slate-800">Drilldown Analysis</h2>
          <p class="text-sm text-slate-500 mt-1">
            <i class="fas fa-info-circle mr-1"></i>
            Click on any bar to drill down into detailed data
          </p>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div class="p-6 lg:p-8">
      
      <!-- Chart 1: Profit Drilldown -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-800">Profit Analysis</h3>
            <p class="text-sm text-slate-500" id="profitBreadcrumb">Region</p>
          </div>
          <button onclick="resetChart('profit')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors text-sm font-medium">
            <i class="fas fa-redo mr-2"></i>Reset
          </button>
        </div>
        <canvas id="profitChart" class="cursor-pointer" style="max-height: 400px;"></canvas>
      </div>

      <!-- Chart 2: Sales Drilldown -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-800">Sales Analysis</h3>
            <p class="text-sm text-slate-500" id="salesBreadcrumb">Region</p>
          </div>
          <button onclick="resetChart('sales')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors text-sm font-medium">
            <i class="fas fa-redo mr-2"></i>Reset
          </button>
        </div>
        <canvas id="salesChart" class="cursor-pointer" style="max-height: 400px;"></canvas>
      </div>

      <!-- Chart 3: Profit Ratio Drilldown -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-800">Profit Ratio Analysis (%)</h3>
            <p class="text-sm text-slate-500" id="ratioBreadcrumb">Region</p>
          </div>
          <button onclick="resetChart('ratio')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors text-sm font-medium">
            <i class="fas fa-redo mr-2"></i>Reset
          </button>
        </div>
        <canvas id="ratioChart" class="cursor-pointer" style="max-height: 400px;"></canvas>
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

    document.addEventListener('click', (e) => {
      if (window.innerWidth < 1024) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          sidebar.classList.add('-translate-x-full');
        }
      }
    });

    // Drilldown State Management
    const drilldownState = {
      profit: { level: 'region', filters: {}, history: [] },
      sales: { level: 'region', filters: {}, history: [] },
      ratio: { level: 'region', filters: {}, history: [] }
    };

    const levelSequence = ['region', 'country', 'state', 'city'];
    
    let profitChart, salesChart, ratioChart;

    // Initialize Charts
    async function initCharts() {
      await loadChartData('profit');
      await loadChartData('sales');
      await loadChartData('ratio');
    }

    // Load Chart Data
    async function loadChartData(metric) {
      const state = drilldownState[metric];
      
      try {
        const response = await fetch('drilldown_api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            level: state.level,
            metric: metric,
            filters: state.filters
          })
        });
        
        const data = await response.json();
        updateChart(metric, data);
        updateBreadcrumb(metric);
      } catch (error) {
        console.error('Error loading data:', error);
      }
    }

    // Update Chart
    function updateChart(metric, data) {
      const labels = data.map(item => item.label);
      const values = data.map(item => parseFloat(item.value));
      
      const colors = {
        profit: '#10b981',
        sales: '#4887ecff',
        ratio: '#f59e0b'
      };

      const chartConfig = {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: metric === 'profit' ? 'Profit ($)' : metric === 'sales' ? 'Sales ($)' : 'Profit Ratio (%)',
            data: values,
            backgroundColor: colors[metric],
            borderRadius: 6,
            hoverBackgroundColor: colors[metric] + 'dd'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          onClick: (event, elements) => {
            if (elements.length > 0) {
              const index = elements[0].index;
              const label = labels[index];
              drillDown(metric, label);
            }
          },
          plugins: {
            legend: { display: true },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) label += ': ';
                  if (metric === 'ratio') {
                    label += context.parsed.y.toFixed(2) + '%';
                  } else {
                    label += '$' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                  }
                  return label;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  if (metric === 'ratio') {
                    return value.toFixed(1) + '%';
                  }
                  return '$' + value.toLocaleString();
                }
              }
            }
          }
        }
      };

      const canvasId = metric + 'Chart';
      const ctx = document.getElementById(canvasId).getContext('2d');
      
      // Destroy existing chart
      if (metric === 'profit' && profitChart) profitChart.destroy();
      if (metric === 'sales' && salesChart) salesChart.destroy();
      if (metric === 'ratio' && ratioChart) ratioChart.destroy();
      
      // Create new chart
      if (metric === 'profit') {
        profitChart = new Chart(ctx, chartConfig);
      } else if (metric === 'sales') {
        salesChart = new Chart(ctx, chartConfig);
      } else if (metric === 'ratio') {
        ratioChart = new Chart(ctx, chartConfig);
      }
    }

    // Drill Down
    function drillDown(metric, label) {
      const state = drilldownState[metric];
      const currentLevelIndex = levelSequence.indexOf(state.level);
      
      // Check if we can drill down further
      if (currentLevelIndex >= levelSequence.length - 1) {
        return; // Already at the deepest level
      }
      
      // Save current state to history
      state.history.push({
        level: state.level,
        filters: {...state.filters}
      });
      
      // Update filters
      state.filters[state.level] = label;
      
      // Move to next level
      state.level = levelSequence[currentLevelIndex + 1];
      
      // Reload data
      loadChartData(metric);
    }

    // Reset Chart
    function resetChart(metric) {
      drilldownState[metric] = { level: 'region', filters: {}, history: [] };
      loadChartData(metric);
    }

    // Update Breadcrumb
    function updateBreadcrumb(metric) {
      const state = drilldownState[metric];
      let breadcrumb = [];
      
      levelSequence.forEach(level => {
        if (state.filters[level]) {
          breadcrumb.push(state.filters[level]);
        }
      });
      
      breadcrumb.push(state.level.charAt(0).toUpperCase() + state.level.slice(1));
      
      document.getElementById(metric + 'Breadcrumb').textContent = breadcrumb.join(' → ');
    }

    // Initialize on page load
    initCharts();
  </script>
</body>
</html>