<?php
session_start();

$namaPengguna = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$emailPengguna = isset($_SESSION['email']) ? $_SESSION['email'] : '-';

?>
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
    <div class="me-3">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
        <span class="icon-menu"></span>
      </button>
    </div>
    <div>
      <a class="navbar-brand brand-logo" href="../index.php">
      <img src="assets/images/logo-mini-reverse.svg" alt="logo" />
      </a>
      <a class="navbar-brand brand-logo-mini" href="#">
        <img src="assets/images/logo-mini-reverse.svg" alt="logo" />
      </a>
    </div>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-top">
    <ul class="navbar-nav">
      <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
        <h1 class="welcome-text">
          <span id="greeting"></span>, <span class="text-black fw-bold"><?php echo htmlspecialchars($namaPengguna); ?></span>
        </h1>

      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown d-none d-lg-block user-dropdown">
        <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <img class="img-xs rounded-circle" src="assets/images/logo-mini-reverse.svg" alt="Profile image" />
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
          <div class="dropdown-header text-center">
            <img class="img-md rounded-circle" src="assets/images/logo-mini-reverse.svg" alt="Profile image" />
            <p class="mb-1 mt-3 font-weight-semibold"><?php echo htmlspecialchars($namaPengguna); ?></p>
            <p class="fw-light text-muted mb-0"><?php echo htmlspecialchars($emailPengguna); ?></p>
          </div>
          <a class="dropdown-item" href="controller/AuthController.php?action=logout">
            <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
          </a>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
      <span class="mdi mdi-menu"></span>
    </button>
  </div>
</nav>
<script>
  const now = new Date();
  const hour = now.getHours();
  let greeting = "Hello";

  if (hour >= 5 && hour < 12) {
    greeting = "Good Morning";
  } else if (hour >= 12 && hour < 17) {
    greeting = "Good Afternoon";
  } else {
    greeting = "Good Evening";
  }

  document.getElementById("greeting").textContent = greeting;
</script>