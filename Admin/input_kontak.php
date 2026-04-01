<?php
include 'template/header.php';
include 'template/menu.php';
?>

<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
      <!--begin::Row-->
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Input Kontak</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Input Kontak</li>
          </ol>
        </div>
      </div>
      <!--end::Row-->
    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content Header-->

  <!--begin::App Content-->
  <div class="app-content">
    <!--begin::Container-->
    <div class="container-fluid">
      <!--begin::Row-->
      <div class="row">
        <div class="col-md-12">

          <!-- Default box -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Form Input Kontak</h3>

              <div class="card-tools">
                <button
                  type="button"
                  class="btn btn-tool"
                  data-lte-toggle="card-collapse"
                  title="Collapse">
                  <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                  <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-tool"
                  data-lte-toggle="card-remove"
                  title="Remove">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
                <div class="col-md-12">

                  <!--begin::Form-->
                  <form action="proses_kontak.php" method="post">
                    <div class="card-body">

                      <div class="mb-3">
                        <label for="exampleInputEmail2" class="form-label">Nama</label>
                        <input
                          type="text"
                          name="nama"
                          class="form-control"
                          id="exampleInputEmail2"
                          aria-describedby="emailHelp"/>
                      </div>

                      <div class="mb-3">
                        <label for="exampleInputPassword2" class="form-label">Email</label>
                        <input
                          type="email"
                          name="email"
                          class="form-control"
                          id="exampleInputPassword2"/>
                      </div>

                      <div class="mb-3">
                        <label for="exampleInputEmail2" class="form-label">Pesan</label>
                        <input
                          name="pesan"
                          class="form-control"
                          id="exampleInputEmail2"
                          aria-describedby="emailHelp">
                      </div>

                      <div class="mb-3">
                        <label for="exampleInputPassword2" class="form-label">Tanggal Kirim</label>
                        <input
                          type="date"
                          name="tanggal_kirim"
                          class="form-control"
                          id="exampleInputPassword2"
                        />
                      </div>

                    </div>
                    <!--end::Body-->

                    <!--begin::Footer-->
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                    <!--end::Footer-->
                  </form>
                  <!--end::Form-->

                </div>
              </div>
            </div>

            <div class="card-footer">
              Footer
            </div>
          </div>
          <!-- /.card -->
        </div>
      </div>
      <!--end::Row-->
    </div>
</div>
  <!--end::App Content-->
</main>

<?php
include 'template/footer.php';
?>
