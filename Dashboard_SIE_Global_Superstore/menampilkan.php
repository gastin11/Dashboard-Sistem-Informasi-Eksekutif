<?php
require 'config.php';

// Filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';

// Paging
$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build query with filters
$where = [];
$params = [];

if ($search) {
    // PERBAIKAN: Menggunakan nama parameter unik untuk setiap posisi placeholder
    $where[] = "(customer_name LIKE :search1 OR product_name LIKE :search2 OR order_id LIKE :search3)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}
if ($category) {
    $where[] = "category = :category";
    $params[':category'] = $category;
}
if ($region) {
    $where[] = "region = :region";
    $params[':region'] = $region;
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM global_superstore $whereClause");
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalRows = $countStmt->fetch()['total'];
$totalPages = ceil($totalRows / $limit);

// Get data
$stmt = $pdo->prepare("SELECT * FROM global_superstore $whereClause ORDER BY order_date DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

// Get filter options
$categories = $pdo->query("SELECT DISTINCT category FROM global_superstore ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$regions = $pdo->query("SELECT DISTINCT region FROM global_superstore ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data Transaksi - Global Superstore</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
  
  <div class="lg:hidden fixed top-4 left-4 z-50">
    <button id="menuToggle" class="bg-purple-600 text-white p-3 rounded-lg shadow-lg">
      <i class="fas fa-bars"></i>
    </button>
  </div>

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
      <a href="menampilkan.php" class="flex items-center gap-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-lg font-medium transition-all">
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
        <p class="text-xs text-slate-600 mb-1">Total Records</p>
        <p class="text-xl font-bold text-purple-700"><?= number_format($totalRows) ?></p>
      </div>
    </div>
  </aside>

  <main class="lg:ml-64 min-h-screen">
    <header class="bg-white shadow-sm border-b border-slate-200 px-6 lg:px-8 py-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-800">Data Transaksi</h2>
          <p class="text-sm text-slate-500 mt-1">
            Menampilkan <?= number_format($totalRows) ?> transaksi dari database
          </p>
        </div>
      </div>
    </header>

    <div class="p-6 lg:p-8">
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <form method="GET" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                <i class="fas fa-search mr-1"></i> Search
              </label>
              <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($search) ?>" 
                placeholder="Customer, Product, Order ID..."
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                <i class="fas fa-tag mr-1"></i> Category
              </label>
              <select 
                name="category"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
              >
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                <i class="fas fa-map-marker-alt mr-1"></i> Region
              </label>
              <select 
                name="region"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
              >
                <option value="">All Regions</option>
                <?php foreach ($regions as $reg): ?>
                  <option value="<?= htmlspecialchars($reg) ?>" <?= $region === $reg ? 'selected' : '' ?>>
                    <?= htmlspecialchars($reg) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
              <i class="fas fa-filter mr-2"></i>Apply Filters
            </button>
            <a href="menampilkan.php" class="px-6 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium">
              <i class="fas fa-redo mr-2"></i>Reset
            </a>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
              <tr>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider">Order ID</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider">Date</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider">Customer</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider hidden lg:table-cell">Region</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider hidden lg:table-cell">Country</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider hidden lg:table-cell">State</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider hidden lg:table-cell">City</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Category</th>
                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider hidden xl:table-cell">Product</th>
                <th class="px-4 py-4 text-right text-xs font-semibold uppercase tracking-wider">Sales</th>
                <th class="px-4 py-4 text-right text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Qty</th>
                <th class="px-4 py-4 text-right text-xs font-semibold uppercase tracking-wider">Profit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                  <tr class="hover:bg-purple-50 transition-colors cursor-pointer">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span class="text-sm font-medium text-purple-600"><?= htmlspecialchars($r['order_id']) ?></span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">
                      <?= date('d/m/Y', strtotime($r['order_date'])) ?>
                    </td>
                    <td class="px-4 py-3">
                      <div class="text-sm font-medium text-slate-800"><?= htmlspecialchars($r['customer_name']) ?></div>
                      <div class="text-xs text-slate-500 lg:hidden"><?= htmlspecialchars($r['region']) ?></div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                      <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                        <?= htmlspecialchars($r['region']) ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                      <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                        <?= htmlspecialchars($r['country']) ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                      <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                        <?= htmlspecialchars($r['state']) ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                      <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                        <?= htmlspecialchars($r['city']) ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap hidden md:table-cell">
                      <span class="px-2 py-1 text-xs font-medium rounded-full 
                        <?php 
                          $categoryColors = [
                            'Technology' => 'bg-blue-100 text-blue-700',
                            'Furniture' => 'bg-green-100 text-green-700',
                            'Office Supplies' => 'bg-amber-100 text-amber-700'
                          ];
                          echo $categoryColors[$r['category']] ?? 'bg-slate-100 text-slate-700';
                        ?>">
                        <?= htmlspecialchars($r['category']) ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700 hidden xl:table-cell max-w-xs truncate">
                      <?= htmlspecialchars($r['product_name']) ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                      <span class="text-sm font-semibold text-slate-800">$<?= number_format($r['sales'], 2) ?></span>
                      <?php if ($r['discount'] > 0): ?>
                        <div class="text-xs text-red-600">-<?= number_format($r['discount'] * 100, 0) ?>%</div>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-slate-600 hidden sm:table-cell">
                      <?= (int)$r['quantity'] ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                      <span class="text-sm font-semibold <?= $r['profit'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        $<?= number_format($r['profit'], 2) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="12" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <i class="fas fa-inbox text-slate-300 text-5xl mb-4"></i>
                      <p class="text-slate-500 text-lg font-medium">No data found</p>
                      <p class="text-slate-400 text-sm mt-1">Try adjusting your filters</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-slate-600">
              Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $totalPages ?></span>
            </div>
            
            <div class="flex items-center gap-2">
              <?php if ($page > 1): ?>
                <a href="?page=1<?= $search ? "&search=$search" : "" ?><?= $category ? "&category=$category" : "" ?><?= $region ? "&region=$region" : "" ?>" 
                   class="px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                  <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?= $page-1 ?><?= $search ? "&search=$search" : "" ?><?= $category ? "&category=$category" : "" ?><?= $region ? "&region=$region" : "" ?>" 
                   class="px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                  <i class="fas fa-angle-left"></i>
                </a>
              <?php endif; ?>

              <?php 
              $start = max(1, $page - 2);
              $end = min($totalPages, $page + 2);
              for ($i = $start; $i <= $end; $i++): 
              ?>
                <a href="?page=<?= $i ?><?= $search ? "&search=$search" : "" ?><?= $category ? "&category=$category" : "" ?><?= $region ? "&region=$region" : "" ?>" 
                   class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-purple-600 text-white font-medium' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' ?> transition-colors">
                  <?= $i ?>
                </a>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?><?= $search ? "&search=$search" : "" ?><?= $category ? "&category=$category" : "" ?><?= $region ? "&region=$region" : "" ?>" 
                   class="px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                  <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?= $totalPages ?><?= $search ? "&search=$search" : "" ?><?= $category ? "&category=$category" : "" ?><?= $region ? "&region=$region" : "" ?>" 
                   class="px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                  <i class="fas fa-angle-double-right"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
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
  </script>
</body>
</html>