<?php
require 'config.php';

// Ambil daftar region untuk dropdown
$regions = $pdo->query("SELECT DISTINCT region FROM global_superstore ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>What-If Analysis - Global Superstore</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
      <a href="drilldown.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
        <i class="fas fa-layer-group"></i>
        <span>Drilldown Analysis</span>
      </a>
      <a href="whatif.php" class="flex items-center gap-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-lg font-medium transition-all">
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
          <h2 class="text-2xl font-bold text-slate-800">Analysis What-If Global Superstore</h2>
          <p class="text-sm text-slate-500 mt-1">
            <i class="fas fa-info-circle mr-1"></i>
            Simulasikan perubahan variabel untuk melihat dampaknya
          </p>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div class="p-6 lg:p-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Card 1: Data Region -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
          <div class="bg-purple-50 -m-6 mb-6 p-4 rounded-t-xl border-b border-green-100">
            <h3 class="text-lg font-semibold text-purple-800">Data Region</h3>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Region</label>
              <select id="regionSelect" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">--Pilih Region--</option>
                <?php foreach ($regions as $region): ?>
                  <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="grid grid-cols-3 gap-4 pt-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Profit Ratio</label>
                <div class="flex items-center gap-2">
                  <span class="text-lg font-bold text-slate-800">:</span>
                  <span id="currentRatio" class="text-lg text-slate-600">-</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Profit</label>
                <div class="flex items-center gap-2">
                  <span class="text-lg font-bold text-slate-800">:</span>
                  <span id="currentProfit" class="text-lg text-slate-600">-</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Sales</label>
                <div class="flex items-center gap-2">
                  <span class="text-lg font-bold text-slate-800">:</span>
                  <span id="currentSales" class="text-lg text-slate-600">-</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Input Analysis -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
          <div class="bg-purple-50 -m-6 mb-6 p-4 rounded-t-xl border-b border-purple-100">
            <h3 class="text-lg font-semibold text-purple-800">Masukkan Data Analysis What If</h3>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Variabel Yang Ingin Diubah</label>
              <select id="variableSelect" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">--Pilih Variabel--</option>
                <option value="ratio">Profit Ratio (%)</option>
                <option value="profit">Profit ($)</option>
                <option value="sales">Sales ($)</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Nilai</label>
              <input type="number" id="newValue" step="0.01" placeholder="Masukkan nilai baru" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <button onclick="analyzeWhatIf()" class="w-full bg-purple-500 hover:bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors mt-6">
              Analysis What If
            </button>
          </div>
        </div>
      </div>

      <!-- Card 3: Hasil Analysis -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="bg-purple-50 -m-6 mb-6 p-4 rounded-t-xl border-b border-purple-100">
          <h3 class="text-lg font-semibold text-purple-800">Hasil Analysis What If</h3>
        </div>

        <div id="resultContainer" class="hidden">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Scenario 1 -->
            <div class="border border-slate-200 rounded-lg p-6 bg-slate-50">
              <h4 class="text-md font-semibold text-slate-700 mb-4" id="scenario1Title">Jika Produktivitas Tetap</h4>
              
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-slate-700 font-medium">Profit Ratio</span>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-800">:</span>
                    <span id="result1Ratio" class="text-lg font-semibold text-green-600">-</span>
                  </div>
                </div>

                <div class="flex justify-between items-center">
                  <span id="result1Label1" class="text-slate-700 font-medium">Produktivitas</span>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-800">:</span>
                    <span id="result1Value1" class="text-lg font-semibold">-</span>
                  </div>
                </div>

                <div class="flex justify-between items-center">
                  <span id="result1Label2" class="text-slate-700 font-medium">Produksi</span>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-800">:</span>
                    <span id="result1Value2" class="text-lg font-semibold text-red-600">-</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Scenario 2 -->
            <div class="border border-slate-200 rounded-lg p-6 bg-slate-50">
              <h4 class="text-md font-semibold text-slate-700 mb-4" id="scenario2Title">Jika Produksi Tetap</h4>
              
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-slate-700 font-medium">Profit Ratio</span>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-800">:</span>
                    <span id="result2Ratio" class="text-lg font-semibold text-green-600">-</span>
                  </div>
                </div>

                <div class="flex justify-between items-center">
                  <span id="result2Label1" class="text-slate-700 font-medium">Produktivitas</span>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-800">:</span>
                    <span id="result2Value1" class="text-lg font-semibold text-red-600">-</span>
                  </div>
                </div>

                <div class="flex justify-between items-center">
                  <span id="result2Label2" class="text-slate-700 font-medium">Produksi</span>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-800">:</span>
                    <span id="result2Value2" class="text-lg font-semibold">-</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div id="emptyState" class="text-center py-12 text-slate-400">
          <i class="fas fa-chart-line text-5xl mb-4"></i>
          <p class="text-lg">Pilih region dan variabel untuk memulai analisis</p>
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

    document.addEventListener('click', (e) => {
      if (window.innerWidth < 1024) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          sidebar.classList.add('-translate-x-full');
        }
      }
    });

    let currentData = null;

    // Load data when region is selected
    document.getElementById('regionSelect').addEventListener('change', async function() {
      const region = this.value;
      if (!region) {
        document.getElementById('currentRatio').textContent = '-';
        document.getElementById('currentProfit').textContent = '-';
        document.getElementById('currentSales').textContent = '-';
        currentData = null;
        return;
      }

      try {
        const response = await fetch('whatif_api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'getRegionData', region: region })
        });
        
        const data = await response.json();
        currentData = data;

        const ratio = (data.profit / data.sales * 100).toFixed(2);
        document.getElementById('currentRatio').textContent = ratio + '%';
        document.getElementById('currentProfit').textContent = '$' + parseFloat(data.profit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('currentSales').textContent = '$' + parseFloat(data.sales).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      } catch (error) {
        console.error('Error loading data:', error);
        alert('Gagal memuat data region');
      }
    });

    // What-If Analysis
    function analyzeWhatIf() {
      const region = document.getElementById('regionSelect').value;
      const variable = document.getElementById('variableSelect').value;
      const newValue = parseFloat(document.getElementById('newValue').value);

      if (!region) {
        alert('Pilih region terlebih dahulu');
        return;
      }

      if (!variable) {
        alert('Pilih variabel yang ingin diubah');
        return;
      }

      if (!newValue || isNaN(newValue)) {
        alert('Masukkan nilai yang valid');
        return;
      }

      if (!currentData) {
        alert('Data belum dimuat');
        return;
      }

      const currentProfit = parseFloat(currentData.profit);
      const currentSales = parseFloat(currentData.sales);
      const currentRatio = (currentProfit / currentSales) * 100;

      let result1 = {}, result2 = {};

      // Calculate based on selected variable
      if (variable === 'ratio') {
        // Scenario 1: Sales tetap, hitung Profit baru
        result1.ratio = newValue;
        result1.sales = currentSales;
        result1.profit = (newValue / 100) * currentSales;

        // Scenario 2: Profit tetap, hitung Sales baru
        result2.ratio = newValue;
        result2.profit = currentProfit;
        result2.sales = (currentProfit / newValue) * 100;

        document.getElementById('scenario1Title').textContent = 'Jika Sales Tetap';
        document.getElementById('scenario2Title').textContent = 'Jika Profit Tetap';
        
        document.getElementById('result1Label1').textContent = 'Sales';
        document.getElementById('result1Label2').textContent = 'Profit';
        document.getElementById('result2Label1').textContent = 'Profit';
        document.getElementById('result2Label2').textContent = 'Sales';

      } else if (variable === 'profit') {
        // Scenario 1: Sales tetap, hitung Ratio baru
        result1.profit = newValue;
        result1.sales = currentSales;
        result1.ratio = (newValue / currentSales) * 100;

        // Scenario 2: Ratio tetap, hitung Sales baru
        result2.profit = newValue;
        result2.ratio = currentRatio;
        result2.sales = (newValue / currentRatio) * 100;

        document.getElementById('scenario1Title').textContent = 'Jika Sales Tetap';
        document.getElementById('scenario2Title').textContent = 'Jika Profit Ratio Tetap';
        
        document.getElementById('result1Label1').textContent = 'Sales';
        document.getElementById('result1Label2').textContent = 'Profit';
        document.getElementById('result2Label1').textContent = 'Profit';
        document.getElementById('result2Label2').textContent = 'Sales';

      } else if (variable === 'sales') {
        // Scenario 1: Profit tetap, hitung Ratio baru
        result1.sales = newValue;
        result1.profit = currentProfit;
        result1.ratio = (currentProfit / newValue) * 100;

        // Scenario 2: Ratio tetap, hitung Profit baru
        result2.sales = newValue;
        result2.ratio = currentRatio;
        result2.profit = (currentRatio / 100) * newValue;

        document.getElementById('scenario1Title').textContent = 'Jika Profit Tetap';
        document.getElementById('scenario2Title').textContent = 'Jika Profit Ratio Tetap';
        
        document.getElementById('result1Label1').textContent = 'Profit';
        document.getElementById('result1Label2').textContent = 'Sales';
        document.getElementById('result2Label1').textContent = 'Sales';
        document.getElementById('result2Label2').textContent = 'Profit';
      }

      // Display results
      displayResults(result1, result2, variable);
    }

    function displayResults(result1, result2, changedVariable) {
      document.getElementById('emptyState').classList.add('hidden');
      document.getElementById('resultContainer').classList.remove('hidden');

      // Result 1
      document.getElementById('result1Ratio').textContent = result1.ratio.toFixed(2) + '%';
      
      if (changedVariable === 'ratio') {
        document.getElementById('result1Value1').textContent = '$' + result1.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result1Value1').className = 'text-lg font-semibold text-slate-800';
        document.getElementById('result1Value2').textContent = '$' + result1.profit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result1Value2').className = 'text-lg font-semibold text-red-600';
      } else if (changedVariable === 'profit') {
        document.getElementById('result1Value1').textContent = '$' + result1.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result1Value1').className = 'text-lg font-semibold text-slate-800';
        document.getElementById('result1Value2').textContent = '$' + result1.profit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result1Value2').className = 'text-lg font-semibold text-red-600';
      } else {
        document.getElementById('result1Value1').textContent = '$' + result1.profit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result1Value1').className = 'text-lg font-semibold text-slate-800';
        document.getElementById('result1Value2').textContent = '$' + result1.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result1Value2').className = 'text-lg font-semibold text-red-600';
      }

      // Result 2
      document.getElementById('result2Ratio').textContent = result2.ratio.toFixed(2) + '%';
      
      if (changedVariable === 'ratio') {
        document.getElementById('result2Value1').textContent = '$' + result2.profit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result2Value1').className = 'text-lg font-semibold text-slate-800';
        document.getElementById('result2Value2').textContent = '$' + result2.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result2Value2').className = 'text-lg font-semibold text-red-600';
      } else if (changedVariable === 'profit') {
        document.getElementById('result2Value1').textContent = '$' + result2.profit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result2Value1').className = 'text-lg font-semibold text-red-600';
        document.getElementById('result2Value2').textContent = '$' + result2.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result2Value2').className = 'text-lg font-semibold text-slate-800';
      } else {
        document.getElementById('result2Value1').textContent = '$' + result2.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result2Value1').className = 'text-lg font-semibold text-red-600';
        document.getElementById('result2Value2').textContent = '$' + result2.profit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('result2Value2').className = 'text-lg font-semibold text-slate-800';
      }
    }
  </script>
</body>
</html>