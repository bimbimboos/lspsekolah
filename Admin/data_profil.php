<?php
include "template/header.php";
include "template/menu.php";
?>

<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Profil Sekolah</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profil Sekolah</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->

  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">

      <?php
      include '../koneksi.php';

      $pesan_sukses = "";
      $pesan_error  = "";

      // Data default SMK Ganesha Tama Boyolali
      $default = [
        'nama_sekolah'   => 'SMK Ganesha Tama Boyolali',
        'npsn'           => '20308460',
        'alamat'         => 'Jl. Perintis Kemerdekaan, Bangunharjo, Pulisen',
        'desa'           => 'Pulisen',
        'kecamatan'      => 'Boyolali',
        'kabupaten'      => 'Boyolali',
        'provinsi'       => 'Jawa Tengah',
        'email'          => 'info@ganeshatama-byi.sch.id',
        'telepon'        => '(0276) 321579',
        'website'        => 'https://ganeshatama-byi.sch.id',
        'kepala_sekolah' => 'Drs. Sriadi Witjitro',
        'visi'           => 'Menghasilkan siswa yang berkarakter, kompeten dan berwawasan lingkungan.',
        'misi'           => "1. Membentuk kesadaran iman, taqwa dan berakhlaq mulia\n2. Membentuk jiwa dan pola pikir kreatif, Inovatif dan kompetitif berbasis ICT\n3. Mengembangkan potensi mandiri, kerja keras yang berwawasan kedepan\n4. Membentuk pribadi yang peka, peduli, bertanggung-jawab terhadap kemanusiaan dan lingkungan",
        'logo'           => '',
        'deskripsi'      => 'SMK Ganesha Tama Boyolali adalah SMK yang bernaung pada Yayasan Pendidikan Tunas Pembangunan. Salah satu Sekolah Menengah Kejuruan di kota Boyolali yang memiliki fasilitas pembelajaran memadai dan Terakreditasi A. Sekolah ini berlisensi Internasional dan berstandar ISO 9001:2015 (SUCOFINDO).',
      ];

      // Proses simpan
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
          $npsn      = mysqli_real_escape_string($koneksi, $_POST['npsn']);
          $alamat    = mysqli_real_escape_string($koneksi, $_POST['alamat']);
          $desa      = mysqli_real_escape_string($koneksi, $_POST['desa']);
          $kecamatan = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
          $kabupaten = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
          $provinsi  = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
          $email     = mysqli_real_escape_string($koneksi, $_POST['email']);
          $telp      = mysqli_real_escape_string($koneksi, $_POST['telepon']);
          $website   = mysqli_real_escape_string($koneksi, $_POST['website']);
          $kepala    = mysqli_real_escape_string($koneksi, $_POST['kepala_sekolah']);
          $visi      = mysqli_real_escape_string($koneksi, $_POST['visi']);
          $misi      = mysqli_real_escape_string($koneksi, $_POST['misi']);
          $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
          $logo_lama = $_POST['logo_lama'];
          $logo      = $logo_lama;

          // Upload logo
          if (!empty($_FILES['logo']['name'])) {
              $ext_allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
              $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
              if (in_array($ext, $ext_allowed)) {
                  $nama_file  = "logo_" . time() . "." . $ext;
                  $upload_dir = "upload/";
                  if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $nama_file)) {
                      if (!empty($logo_lama) && file_exists($upload_dir . $logo_lama)) {
                          unlink($upload_dir . $logo_lama);
                      }
                      $logo = $nama_file;
                  } else {
                      $pesan_error = "Gagal mengupload logo.";
                  }
              } else {
                  $pesan_error = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
              }
          }

          if (empty($pesan_error)) {
              $cek = mysqli_query($koneksi, "SELECT id FROM profil_sekolah LIMIT 1");
              if (mysqli_num_rows($cek) > 0) {
                  $row = mysqli_fetch_assoc($cek);
                  $id  = $row['id'];
                  $sql = "UPDATE profil_sekolah SET
                              nama_sekolah   = '$nama',
                              npsn           = '$npsn',
                              alamat         = '$alamat',
                              desa           = '$desa',
                              kecamatan      = '$kecamatan',
                              kabupaten      = '$kabupaten',
                              provinsi       = '$provinsi',
                              email          = '$email',
                              telepon        = '$telp',
                              website        = '$website',
                              kepala_sekolah = '$kepala',
                              visi           = '$visi',
                              misi           = '$misi',
                              logo           = '$logo',
                              deskripsi      = '$deskripsi'
                          WHERE id = $id";
              } else {
                  $sql = "INSERT INTO profil_sekolah
                              (nama_sekolah, npsn, alamat, desa, kecamatan, kabupaten, provinsi, email, telepon, website, kepala_sekolah, visi, misi, logo, deskripsi)
                          VALUES
                              ('$nama', '$npsn', '$alamat', '$desa', '$kecamatan', '$kabupaten', '$provinsi', '$email', '$telp', '$website', '$kepala', '$visi', '$misi', '$logo', '$deskripsi')";
              }

              if (mysqli_query($koneksi, $sql)) {
                  $pesan_sukses = "Data profil berhasil disimpan!";
              } else {
                  $pesan_error = "Gagal menyimpan: " . mysqli_error($koneksi);
              }
          }
      }

      // Ambil data profil dari DB, fallback ke default jika kosong
      $data   = mysqli_query($koneksi, "SELECT * FROM profil_sekolah LIMIT 1");
      $profil = mysqli_fetch_array($data);

      // Jika tabel kosong, pakai data default
      if (!$profil) {
          $profil = $default;
      }
      ?>

      <!-- Notifikasi -->
      <?php if (!empty($pesan_sukses)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i><?= $pesan_sukses ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $pesan_error ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- ══ TAMPILAN PROFIL ══ -->
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title mb-0">Profil Sekolah</h3>
              <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formEdit">
                <i class="bi bi-pencil-square me-1"></i> Edit Profil
              </button>
            </div>
            <div class="card-body">
              <div class="row align-items-start">

                <!-- Logo -->
                <div class="col-md-2 text-center mb-3">
                  <?php if (!empty($profil['logo']) && file_exists("upload/" . $profil['logo'])): ?>
                    <img src="upload/<?= htmlspecialchars($profil['logo']) ?>"
                      class="img-thumbnail" style="max-height:120px;">
                  <?php else: ?>
                    <div class="bg-light border rounded d-flex align-items-center justify-content-center"
                      style="width:100px;height:100px;margin:auto;">
                      <i class="bi bi-building fs-1 text-secondary"></i>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Info Utama -->
                <div class="col-md-10">
                  <h4 class="fw-bold mb-1"><?= htmlspecialchars($profil['nama_sekolah']) ?></h4>
                  <p class="text-muted mb-2">
                    <i class="bi bi-geo-alt-fill me-1"></i>
                    <?= htmlspecialchars($profil['alamat']) ?>,
                    <?= htmlspecialchars($profil['desa']) ?>,
                    Kec. <?= htmlspecialchars($profil['kecamatan']) ?>,
                    Kab. <?= htmlspecialchars($profil['kabupaten']) ?>,
                    <?= htmlspecialchars($profil['provinsi']) ?>
                  </p>
                  <div class="row g-2 text-sm">
                    <div class="col-md-4">
                      <small class="text-muted">NPSN</small><br>
                      <strong><?= htmlspecialchars($profil['npsn']) ?></strong>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Telepon</small><br>
                      <strong><?= htmlspecialchars($profil['telepon']) ?></strong>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Email</small><br>
                      <strong><?= htmlspecialchars($profil['email']) ?></strong>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Website</small><br>
                      <a href="<?= htmlspecialchars($profil['website']) ?>" target="_blank">
                        <?= htmlspecialchars($profil['website']) ?>
                      </a>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Kepala Sekolah</small><br>
                      <strong><?= htmlspecialchars($profil['kepala_sekolah']) ?></strong>
                    </div>
                  </div>
                </div>
              </div>

              <hr>

              <!-- Deskripsi -->
              <div class="mb-3">
                <h6 class="fw-bold text-secondary text-uppercase" style="font-size:.75rem;letter-spacing:.07em;">Deskripsi</h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($profil['deskripsi'])) ?></p>
              </div>

              <!-- Visi Misi -->
              <div class="row">
                <div class="col-md-6">
                  <h6 class="fw-bold text-secondary text-uppercase" style="font-size:.75rem;letter-spacing:.07em;">Visi</h6>
                  <p><?= nl2br(htmlspecialchars($profil['visi'])) ?></p>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-bold text-secondary text-uppercase" style="font-size:.75rem;letter-spacing:.07em;">Misi</h6>
                  <p><?= nl2br(htmlspecialchars($profil['misi'])) ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ FORM EDIT (collapse) ══ -->
      <div class="collapse" id="formEdit">
        <div class="row">
          <div class="col-md-12">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Edit Profil Sekolah</h3>
              </div>
              <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="logo_lama" value="<?= htmlspecialchars($profil['logo'] ?? '') ?>">

                  <div class="row g-3">

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Nama Sekolah</label>
                      <input type="text" name="nama_sekolah" class="form-control"
                        value="<?= htmlspecialchars($profil['nama_sekolah'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">NPSN</label>
                      <input type="text" name="npsn" class="form-control"
                        value="<?= htmlspecialchars($profil['npsn'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                      <label class="form-label fw-semibold">Alamat</label>
                      <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($profil['alamat'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Desa</label>
                      <input type="text" name="desa" class="form-control"
                        value="<?= htmlspecialchars($profil['desa'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Kecamatan</label>
                      <input type="text" name="kecamatan" class="form-control"
                        value="<?= htmlspecialchars($profil['kecamatan'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Kabupaten / Kota</label>
                      <input type="text" name="kabupaten" class="form-control"
                        value="<?= htmlspecialchars($profil['kabupaten'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Provinsi</label>
                      <input type="text" name="provinsi" class="form-control"
                        value="<?= htmlspecialchars($profil['provinsi'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Email</label>
                      <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($profil['email'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-semibold">No. Telepon</label>
                      <input type="text" name="telepon" class="form-control"
                        value="<?= htmlspecialchars($profil['telepon'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Website</label>
                      <input type="text" name="website" class="form-control"
                        value="<?= htmlspecialchars($profil['website'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Kepala Sekolah</label>
                      <input type="text" name="kepala_sekolah" class="form-control"
                        value="<?= htmlspecialchars($profil['kepala_sekolah'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Visi</label>
                      <textarea name="visi" class="form-control" rows="4"><?= htmlspecialchars($profil['visi'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Misi</label>
                      <textarea name="misi" class="form-control" rows="4"><?= htmlspecialchars($profil['misi'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                      <label class="form-label fw-semibold">Deskripsi</label>
                      <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($profil['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Logo Sekolah</label>
                      <input type="file" name="logo" class="form-control" accept="image/*"
                        onchange="previewLogo(this)">
                      <div class="form-text">Format: JPG, PNG, GIF, WEBP.</div>
                    </div>

                    <div class="col-md-6 text-center">
                      <?php if (!empty($profil['logo']) && file_exists("upload/" . $profil['logo'])): ?>
                        <label class="form-label fw-semibold d-block">Logo Saat Ini</label>
                        <img id="preview-logo" src="upload/<?= htmlspecialchars($profil['logo']) ?>"
                          width="100" class="img-thumbnail rounded">
                      <?php else: ?>
                        <img id="preview-logo" src="" width="100"
                          class="img-thumbnail rounded" style="display:none;">
                      <?php endif; ?>
                    </div>

                    <div class="col-12 text-end">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy-fill me-1"></i> Simpan Perubahan
                      </button>
                    </div>

                  </div>
                </form>
              </div>
              <div class="card-footer">Pastikan data profil selalu diperbarui.</div>
            </div>
          </div>
        </div>
      </div>
      <!-- ══ END FORM EDIT ══ -->

    </div>
  </div>
  <!--end::App Content-->
</main>

<script>
  function previewLogo(input) {
    const preview = document.getElementById('preview-logo');
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

<?php include "template/footer.php"; ?>