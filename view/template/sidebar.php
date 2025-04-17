<?php
$menu = [
  [
    'title' => 'Dashboard',
    'icon' => 'mdi-view-dashboard',
    'url' => 'index.php?page=dashboard',
    'level' => ['admin', 'kasir', 'pimpinan']
  ],
  [
    'title' => 'Barang',
    'icon' => 'mdi-cube-outline',
    'url' => 'index.php?page=barang',
    'level' => ['admin']
  ],
  [
    'title' => 'Penjualan',
    'icon' => 'mdi-cart-outline',
    'url' => 'index.php?page=penjualan',
    'level' => ['admin', 'kasir']
  ],
  [
    'title' => 'Inventory',
    'icon' => 'mdi-warehouse',
    'url' => 'index.php?page=inventory',
    'level' => ['admin']
  ],
  [
    'title' => 'Perhitungan WAC',
    'icon' => 'mdi-calculator-variant-outline',
    'url' => 'index.php?page=wac',
    'level' => ['admin', 'pimpinan']
  ],
  [
    'title' => 'Laporan',
    'icon' => 'mdi-file-chart-outline',
    'url' => 'index.php?page=laporan',
    'level' => ['admin', 'pimpinan']
  ]
];

$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$userLevel = isset($_SESSION['level']) ? $_SESSION['level'] : '';
?>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <?php foreach ($menu as $item): ?>
      <?php if (in_array($userLevel, $item['level'])): ?>
        <li class="nav-item">
          <?php if (empty($item['children'])): ?>
            <a class="nav-link <?php echo $currentPage == basename($item['url'], '.php') ? 'active' : ''; ?>" href="<?php echo $item['url']; ?>">
              <i class="mdi <?php echo $item['icon']; ?> menu-icon"></i>
              <span class="menu-title"><?php echo $item['title']; ?></span>
            </a>
          <?php else: ?>
            <a class="nav-link" data-bs-toggle="collapse" href="#menu-<?php echo strtolower($item['title']); ?>" aria-expanded="false">
              <i class="menu-icon mdi <?php echo $item['icon']; ?>"></i>
              <span class="menu-title"><?php echo $item['title']; ?></span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="menu-<?php echo strtolower($item['title']); ?>">
              <ul class="nav flex-column sub-menu">
                <?php foreach ($item['children'] as $child): ?>
                  <?php if (in_array($userLevel, $child['level'])): ?>
                    <li class="nav-item">
                      <a class="nav-link <?php echo $currentPage == basename($child['url'], '.php') ? 'active' : ''; ?>" href="<?php echo $child['url']; ?>">
                        <?php echo $child['title']; ?>
                      </a>
                    </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</nav>

<style>
.sidebar .nav .nav-item .nav-link:hover {
  background-color: rgba(108, 114, 147, 0.1);
  color: #0d6efd;
  transition: all 0.3s ease;
  border-left: 3px solid #0d6efd;
}

.sidebar .nav .nav-item .collapse .nav-item .nav-link:hover {
  background-color: rgba(108, 114, 147, 0.1);
  color: #0d6efd;
  transition: all 0.3s ease;
}

.sidebar .nav .nav-item .nav-link:hover i {
  color: #0d6efd;
}

.sidebar .nav .nav-item .nav-link.active {
  background-color: rgba(108, 114, 147, 0.2);
  color: #0d6efd;
  border-left: 3px solid #0d6efd;
}

.sidebar .nav .nav-item .collapse .nav-item .nav-link.active {
  background-color: rgba(108, 114, 147, 0.2);
  color: #0d6efd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var currentLocation = window.location.href;
  var navLinks = document.querySelectorAll('.sidebar .nav .nav-link');
  
  navLinks.forEach(function(link) {
    if (link.href === currentLocation) {
      link.classList.add('active');
      
      var parentCollapse = link.closest('.collapse');
      if (parentCollapse) {
        parentCollapse.classList.add('show');
        var parentToggle = document.querySelector('[href="#' + parentCollapse.id + '"]');
        if (parentToggle) {
          parentToggle.setAttribute('aria-expanded', 'true');
        }
      }
    }
  });
});
</script>