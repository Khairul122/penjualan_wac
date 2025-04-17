<?php include 'template/header.php'; ?>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <!-- <div class="brand-logo text-center mb-3">
                <img src="../assets/images/logo.svg" alt="logo" width="100" />
              </div> -->
              <h4 class="text-center">Selamat datang kembali</h4>
              <h6 class="fw-light text-center">Masukkan username & password</h6>
              <form class="pt-3" method="POST" action="controller/AuthController.php">
                <div class="form-group">
                  <input
                    type="text"
                    class="form-control form-control-lg"
                    name="username"
                    placeholder="Username"
                    required
                  />
                </div>
                <div class="form-group">
                  <input
                    type="password"
                    class="form-control form-control-lg"
                    name="password"
                    placeholder="Password"
                    required
                  />
                </div>
                <div class="mt-3 justify-content-center d-flex">
                  <button
                    type="submit"
                    class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn"
                  >
                    LOGIN
                  </button>
                </div>
                <?php if (isset($_GET['error'])): ?>
                  <div class="alert alert-danger text-center mt-3">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                  </div>
                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="../assets/js/off-canvas.js"></script>
  <script src="../assets/js/hoverable-collapse.js"></script>
  <script src="../assets/js/template.js"></script>
</body>
</html>
