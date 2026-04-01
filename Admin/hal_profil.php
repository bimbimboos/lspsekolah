<?php
ob_start();
session_start();
include "template/header.php";
include "template/menu.php";
include "../koneksi.php";

/* ============================================================
   CSRF TOKEN
   ============================================================ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function cek_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:40px;color:#dc2626;">403 - Token tidak valid. <a href="hal_profil.php">Kembali</a></p>');
    }
}

function set_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/* ============================================================
   AMBIL DATA ADMIN YANG SEDANG LOGIN
   Asumsi: ID admin disimpan di $_SESSION['id_users']
   Jika kamu pakai nama session yang berbeda, sesuaikan di sini.
   ============================================================ */
$id_users = isset($_SESSION['id_users']) ? (int)$_SESSION['id_users'] : 1; // fallback ke 1 untuk demo
$users    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$id_users"));

if (!$users) {
    die('<p style="font-family:sans-serif;padding:40px;">Data admin tidak ditemukan.</p>');
}

/* ============================================================
   PROSES EDIT PROFIL (nama + username)
   ============================================================ */
if (isset($_POST['edit_profil'])) {
    cek_csrf();

    $nama_admin = trim(mysqli_real_escape_string($koneksi, $_POST['nama_admin']));
    $username   = trim(mysqli_real_escape_string($koneksi, $_POST['username']));

    if (empty($nama_admin) || empty($username)) {
        set_flash('danger', 'Nama dan username tidak boleh kosong.');
        header("Location: hal_profil.php"); exit;
    }

    // Cek apakah username sudah dipakai admin lain
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id FROM users
       WHERE username='$username' AND id != $id_users"));
    if ($cek) {
        set_flash('danger', 'Username sudah digunakan oleh admin lain.');
        header("Location: hal_profil.php"); exit;
    }

    mysqli_query($koneksi,
        "UPDATE admin SET nama_admin='$nama_admin', username='$username' WHERE id=$id_users");

    // Update session jika kamu menyimpan nama di sesi
    $_SESSION['nama_admin'] = $nama_admin;
    $_SESSION['username']   = $username;

    set_flash('success', 'Profil berhasil diperbarui.');
    header("Location: hal_profil.php"); exit;
}

/* ============================================================
   PROSES GANTI PASSWORD
   ============================================================ */
if (isset($_POST['ganti_password'])) {
    cek_csrf();

    $pass_lama  = $_POST['pass_lama'];
    $pass_baru  = $_POST['pass_baru'];
    $pass_ulang = $_POST['pass_ulang'];

    // Ambil data fresh dari DB (bukan dari variable $users yang mungkin stale)
    $row = mysqli_fetch_assoc(mysqli_query($koneksi, 
        "SELECT password FROM users WHERE id=$id_users"));

    if (
        password_verify($pass_lama, $row['password']) ||
        md5($pass_lama) === $row['password'] ||
        $pass_lama === $row['password']
    ) {
        if ($pass_baru !== $pass_ulang) {
            set_flash('danger', 'Konfirmasi password tidak sama.');
            header("Location: hal_profil.php"); exit; // ✅ pakai flash + redirect
        }

        if (strlen($pass_baru) < 6) {
            set_flash('danger', 'Password minimal 6 karakter.');
            header("Location: hal_profil.php"); exit;
        }

        $hash = password_hash($pass_baru, PASSWORD_DEFAULT);

        // ✅ Pastikan update ke tabel yang benar
        mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE id=$id_users");

        // ✅ Jangan echo dulu, langsung redirect
        set_flash('success', 'Password berhasil diganti. Silakan login ulang.');
        session_destroy(); // Recommended: paksa login ulang
        header("Location: ../index.php"); exit;

    } else {
        set_flash('danger', 'Password lama salah.');
        header("Location: hal_profil.php"); exit;
    }
}
/* ============================================================
   PROSES UPLOAD FOTO PROFIL
   ============================================================ */
if (isset($_POST['upload_foto'])) {
    cek_csrf();

    // Pastikan kolom foto ada di tabel admin.
    // Jika belum: ALTER TABLE admin ADD COLUMN foto VARCHAR(255) DEFAULT NULL;
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        set_flash('danger', 'Gagal mengupload foto. Coba lagi.');
        header("Location: hal_profil.php"); exit;
    }

    $file     = $_FILES['foto'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($ext, $allowed)) {
        set_flash('danger', 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.');
        header("Location: hal_profil.php"); exit;
    }
    if ($file['size'] > $max_size) {
        set_flash('danger', 'Ukuran file maksimal 2MB.');
        header("Location: hal_profil.php"); exit;
    }

    // Validasi bahwa file benar-benar gambar (bukan file berbahaya)
    $info = getimagesize($file['tmp_name']);
    if ($info === false) {
        set_flash('danger', 'File bukan gambar yang valid.');
        header("Location: hal_profil.php"); exit;
    }

    $folder   = '../uploads/foto_admin/';
    if (!is_dir($folder)) mkdir($folder, 0755, true);

    // Hapus foto lama jika ada
    if (!empty($users['foto']) && file_exists($folder . $users['foto'])) {
        unlink($folder . $users['foto']);
    }

    $nama_file = 'admin_' . $id_users . '_' . time() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $folder . $nama_file);

    $nama_file_db = mysqli_real_escape_string($koneksi, $nama_file);
    mysqli_query($koneksi, "UPDATE admin SET foto='$nama_file_db' WHERE id=$id_users");

    set_flash('success', 'Foto profil berhasil diperbarui.');
    header("Location: hal_profil.php"); exit;
}

/* Refresh data setelah kemungkinan update */
$users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$id_users"));

/* Flash message */
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);

/* Inisial nama untuk avatar fallback */
$inisial = strtoupper(substr($users['nama_admin'] ?? $users['username'], 0, 2));

/* Path foto */
$foto_path = (!empty($users['foto']) && file_exists('../uploads/foto_admin/' . $users['foto']))
    ? '../uploads/foto_admin/' . $users['foto']
    : null;
?>

<style>
:root {
  --primary: #1e40af;
  --primary-lt: #3b82f6;
  --primary-xs: #eff6ff;
  --danger: #ef4444;
  --success: #10b981;
  --warning: #f59e0b;
  --dark: #0f172a;
  --text: #1e293b;
  --muted: #64748b;
  --border: #e2e8f0;
  --surface: #f8fafc;
  --white: #ffffff;
  --radius: 14px;
  --radius-sm: 9px;
  --shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
  --shadow-md: 0 4px 20px rgba(0,0,0,.10);
}

/* Layout */
.profil-grid {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 22px;
  align-items: start;
}
@media (max-width: 900px) {
  .profil-grid { grid-template-columns: 1fr; }
}

/* Card */
.card-profil {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.card-head {
  padding: 16px 22px 14px;
  border-bottom: 1.5px solid var(--border);
  display: flex;
  align-items: center;
  gap: 10px;
}
.card-head-icon {
  width: 34px; height: 34px;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 15px;
}
.card-head-icon.blue  { background:#eff6ff; color:#1e40af; }
.card-head-icon.amber { background:#fffbeb; color:#d97706; }
.card-head-icon.red   { background:#fff1f2; color:#dc2626; }
.card-body-p { padding: 22px; }

/* Avatar besar */
.avatar-wrap {
  position: relative;
  width: 110px; height: 110px;
  margin: 0 auto 16px;
}
.avatar-img {
  width: 110px; height: 110px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--border);
}
.avatar-fallback {
  width: 110px; height: 110px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-lt));
  color: #fff;
  font-size: 2rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid var(--border);
  letter-spacing: -1px;
}
.avatar-edit-btn {
  position: absolute;
  bottom: 4px; right: 4px;
  width: 30px; height: 30px;
  border-radius: 50%;
  background: var(--primary);
  color: #fff;
  border: 2px solid #fff;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px;
  transition: background .18s;
}
.avatar-edit-btn:hover { background: #1d4ed8; }

/* Info list di sidebar */
.info-list { list-style: none; padding: 0; margin: 0; }
.info-list li {
  padding: 11px 0;
  border-bottom: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.info-list li:last-child { border-bottom: none; }
.info-list .lbl {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .05em;
  text-transform: uppercase;
  color: var(--muted);
}
.info-list .val {
  font-size: 14px;
  font-weight: 600;
  color: var(--dark);
}

/* Badge role */
.badge-role {
  display: inline-block;
  background: #eff6ff;
  color: #1e40af;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  border: 1px solid #bfdbfe;
  letter-spacing: .03em;
}

/* Form */
.form-label-custom {
  font-size: 12px;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 6px;
  display: block;
}
.form-control-custom {
  width: 100%;
  padding: 10px 13px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 14px;
  color: var(--text);
  background: var(--white);
  transition: border-color .18s, box-shadow .18s;
  outline: none;
  font-family: inherit;
  box-sizing: border-box;
}
.form-control-custom:focus {
  border-color: var(--primary-lt);
  box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.form-control-custom.error { border-color: var(--danger); }

/* Password field wrapper */
.pass-wrap { position: relative; }
.pass-wrap .toggle-pass {
  position: absolute;
  right: 11px; top: 50%;
  transform: translateY(-50%);
  background: none; border: none;
  color: var(--muted); cursor: pointer;
  font-size: 15px; padding: 0;
  transition: color .18s;
}
.pass-wrap .toggle-pass:hover { color: var(--primary); }

/* Strength bar */
.strength-bar {
  height: 4px;
  border-radius: 4px;
  background: var(--border);
  margin-top: 6px;
  overflow: hidden;
}
.strength-fill {
  height: 100%;
  border-radius: 4px;
  width: 0%;
  transition: width .3s, background .3s;
}
.strength-label {
  font-size: 11px;
  margin-top: 4px;
  font-weight: 600;
  color: var(--muted);
}

/* Tombol submit */
.btn-submit {
  padding: 10px 24px;
  border-radius: var(--radius-sm);
  border: none;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all .18s;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-family: inherit;
}
.btn-primary-custom { background: var(--primary); color: #fff; }
.btn-primary-custom:hover { background: #1d4ed8; }
.btn-danger-custom { background: #dc2626; color: #fff; }
.btn-danger-custom:hover { background: #b91c1c; }

/* Flash */
.flash-alert {
  border-radius: var(--radius-sm);
  padding: 12px 18px;
  margin-bottom: 20px;
  font-size: 13.5px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
}
.flash-success { background:#ecfdf5; border:1.5px solid #a7f3d0; color:#065f46; }
.flash-danger  { background:#fff1f2; border:1.5px solid #fecaca; color:#991b1b; }

/* Upload zone */
.upload-zone {
  border: 2px dashed var(--border);
  border-radius: var(--radius-sm);
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: border-color .18s, background .18s;
}
.upload-zone:hover { border-color: var(--primary-lt); background: var(--primary-xs); }
.upload-zone input[type=file] { display: none; }
.upload-zone .uz-icon { font-size: 1.8rem; color: var(--muted); margin-bottom: 8px; }
.upload-zone .uz-text { font-size: 13px; color: var(--muted); }
.upload-zone .uz-text b { color: var(--primary); }
#preview-foto {
  width: 80px; height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border);
  display: none;
  margin: 0 auto 10px;
}

/* Divider */
.section-divider {
  border: none;
  border-top: 1.5px solid var(--border);
  margin: 22px 0;
}

/* Page header */
.pg-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 24px;
}
.pg-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: 10px;
}
.pg-title .icon-wrap {
  width: 40px; height: 40px;
  background: var(--primary);
  border-radius: 9px;
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 1.1rem;
}
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-3">

  <!-- PAGE HEADER -->
  <div class="pg-header">
    <div class="pg-title">
      <div class="icon-wrap"><i class="bi bi-person-circle"></i></div>
      <div>
        Profil Admin
        <div style="font-size:.82rem;font-weight:400;color:var(--muted);margin-top:1px;">Kelola informasi akun dan keamanan</div>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:.82rem;">
        <li class="breadcrumb-item"><a href="hal_profil.php" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Profil Admin</li>
      </ol>
    </nav>
  </div>

  <!-- FLASH -->
  <?php if ($flash): ?>
  <div class="flash-alert flash-<?= $flash['type'] ?>">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
  </div>
  <?php endif; ?>

  <div class="profil-grid">

    <!-- ========== KOLOM KIRI: Kartu Identitas ========== -->
    <div>

      <!-- Kartu foto & info -->
      <div class="card-profil">
        <div style="background: linear-gradient(135deg,#1e40af,#3b82f6); padding: 28px 22px 20px; text-align:center;">
          <div class="avatar-wrap" style="margin-bottom:12px;">
            <?php if ($foto_path): ?>
              <img src="<?= htmlspecialchars($foto_path) ?>" class="avatar-img" alt="Foto Profil">
            <?php else: ?>
              <div class="avatar-fallback"><?= $inisial ?></div>
            <?php endif; ?>
            <!-- Tombol edit foto kecil -->
            <button class="avatar-edit-btn" onclick="document.getElementById('modalFoto').style.display='flex'" title="Ganti Foto">
              <i class="bi bi-camera-fill"></i>
            </button>
          </div>
          <div style="font-size:17px;font-weight:700;color:#fff;"><?= htmlspecialchars($users['nama_admin'] ?? $users['username']) ?></div>
          <div style="margin-top:6px;"><span class="badge-role" style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.3);">Administrator</span></div>
        </div>

        <div class="card-body-p">
          <ul class="info-list">
            <li>
              <span class="lbl">ID Admin</span>
              <span class="val" style="font-family:monospace;font-size:13px;">#<?= str_pad($users['id'], 4, '0', STR_PAD_LEFT) ?></span>
            </li>
            <li>
              <span class="lbl">Username</span>
              <span class="val">@<?= htmlspecialchars($users['username']) ?></span>
            </li>
            <li>
              <span class="lbl">Nama Lengkap</span>
              <span class="val"><?= htmlspecialchars($users['nama_admin'] ?? '-') ?></span>
            </li>
            <li>
              <span class="lbl">Role</span>
              <span class="val"><span class="badge-role">Super Admin</span></span>
            </li>
          </ul>
        </div>
      </div>

    </div>
    <!-- ========== END KOLOM KIRI ========== -->

    <!-- ========== KOLOM KANAN: Form ========== -->
    <div style="display:flex;flex-direction:column;gap:20px;">

      <!-- FORM EDIT PROFIL -->
      <div class="card-profil">
        <div class="card-head">
          <div class="card-head-icon blue"><i class="bi bi-pencil-fill"></i></div>
          <div>
            <div style="font-weight:700;font-size:14.5px;color:var(--dark);">Edit Informasi Profil</div>
            <div style="font-size:12px;color:var(--muted);">Perbarui nama dan username akun kamu</div>
          </div>
        </div>
        <div class="card-body-p">
          <form method="POST" action="hal_profil.php">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <label class="form-label-custom">Nama Lengkap</label>
                <input type="text" name="nama_admin" class="form-control-custom"
                  value="<?= htmlspecialchars($users['nama_admin'] ?? '') ?>"
                  placeholder="Masukkan nama lengkap" required maxlength="100">
              </div>
              <div>
                <label class="form-label-custom">Username</label>
                <div style="position:relative;">
                  <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px;">@</span>
                  <input type="text" name="username" class="form-control-custom" style="padding-left:28px;"
                    value="<?= htmlspecialchars($users['username']) ?>"
                    placeholder="username" required maxlength="50">
                </div>
              </div>
            </div>

            <hr class="section-divider">

            <div style="display:flex;justify-content:flex-end;">
              <button type="submit" name="edit_profil" class="btn-submit btn-primary-custom">
                <i class="bi bi-check-lg"></i> Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- FORM GANTI PASSWORD -->
      <div class="card-profil">
        <div class="card-head">
          <div class="card-head-icon red"><i class="bi bi-shield-lock-fill"></i></div>
          <div>
            <div style="font-weight:700;font-size:14.5px;color:var(--dark);">Ganti Password</div>
            <div style="font-size:12px;color:var(--muted);">Gunakan password yang kuat dan unik</div>
          </div>
        </div>
        <div class="card-body-p">
          <form method="POST" action="hal_profil.php" id="formPassword">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

              <div>
                <label class="form-label-custom">Password Lama</label>
                <div class="pass-wrap">
                  <input type="password" name="pass_lama" id="passLama" class="form-control-custom" placeholder="••••••••" required>
                  <button type="button" class="toggle-pass" onclick="togglePass('passLama', this)">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>

              <div>
                <label class="form-label-custom">Password Baru</label>
                <div class="pass-wrap">
                  <input type="password" name="pass_baru" id="passBaru" class="form-control-custom"
                    placeholder="Min. 6 karakter" required oninput="cekKekuatan(this.value)">
                  <button type="button" class="toggle-pass" onclick="togglePass('passBaru', this)">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-label" id="strengthLabel"></div>
              </div>

              <div>
                <label class="form-label-custom">Konfirmasi Password</label>
                <div class="pass-wrap">
                  <input type="password" name="pass_ulang" id="passUlang" class="form-control-custom"
                    placeholder="Ulangi password baru" required oninput="cekKonfirmasi()">
                  <button type="button" class="toggle-pass" onclick="togglePass('passUlang', this)">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div style="font-size:11px;margin-top:5px;" id="konfirmasiLabel"></div>
              </div>

            </div>

            <!-- Tips keamanan -->
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:11px 14px;margin-top:16px;font-size:12.5px;color:#92400e;display:flex;align-items:flex-start;gap:8px;">
              <i class="bi bi-lightbulb-fill" style="margin-top:1px;flex-shrink:0;"></i>
              <span>Tips: Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol agar password lebih aman.</span>
            </div>

            <hr class="section-divider">

            <div style="display:flex;justify-content:flex-end;">
              <button type="submit" name="ganti_password" class="btn-submit btn-danger-custom">
                <i class="bi bi-shield-check"></i> Simpan Password
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
    <!-- ========== END KOLOM KANAN ========== -->

  </div><!-- end profil-grid -->

</div>
</div>
</main>

<!-- ============================================================
     MODAL UPLOAD FOTO — custom overlay (bukan Bootstrap modal)
     ============================================================ -->
<div id="modalFoto" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:14px;width:100%;max-width:420px;box-shadow:0 8px 40px rgba(0,0,0,.18);overflow:hidden;">

    <div style="padding:18px 22px;border-bottom:1.5px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
      <div style="font-weight:700;font-size:15px;color:#0f172a;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-camera-fill" style="color:#1e40af;"></i> Ganti Foto Profil
      </div>
      <button onclick="document.getElementById('modalFoto').style.display='none'"
        style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;line-height:1;">&times;</button>
    </div>

    <form method="POST" action="hal_profil.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div style="padding:22px;">
        <img id="preview-foto" src="" alt="Preview">

        <div class="upload-zone" onclick="document.getElementById('inputFoto').click()" id="uploadZone">
          <input type="file" name="foto" id="inputFoto" accept="image/jpeg,image/png,image/webp" onchange="previewFoto(this)">
          <div class="uz-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
          <div class="uz-text"><b>Klik untuk pilih foto</b><br>JPG, PNG, atau WEBP — Maks. 2MB</div>
        </div>

        <div style="font-size:12px;color:#94a3b8;margin-top:10px;text-align:center;">
          Foto akan ditampilkan sebagai avatar profil kamu
        </div>
      </div>

      <div style="padding:14px 22px;border-top:1.5px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" onclick="document.getElementById('modalFoto').style.display='none'"
          style="padding:9px 20px;border-radius:9px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
          Batal
        </button>
        <button type="submit" name="upload_foto" id="btnUpload" disabled
          style="padding:9px 20px;border-radius:9px;border:none;background:#1e40af;color:#fff;font-size:13px;font-weight:600;cursor:pointer;opacity:.5;font-family:inherit;transition:opacity .18s;">
          <i class="bi bi-upload me-1"></i> Upload Foto
        </button>
      </div>
    </form>
  </div>
</div>

<script>
/* Toggle tampilkan/sembunyikan password */
function togglePass(id, btn) {
  const input = document.getElementById(id);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}

/* Indikator kekuatan password */
function cekKekuatan(val) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const map = [
    { w:'0%',   bg:'transparent', txt:'' },
    { w:'25%',  bg:'#ef4444',     txt:'Sangat Lemah' },
    { w:'50%',  bg:'#f59e0b',     txt:'Lemah' },
    { w:'70%',  bg:'#3b82f6',     txt:'Cukup' },
    { w:'85%',  bg:'#10b981',     txt:'Kuat' },
    { w:'100%', bg:'#059669',     txt:'Sangat Kuat' },
  ];
  const m = map[Math.min(score, 5)];
  fill.style.width      = m.w;
  fill.style.background = m.bg;
  label.textContent     = m.txt;
  label.style.color     = m.bg;
  cekKonfirmasi();
}

/* Cek kesesuaian konfirmasi password */
function cekKonfirmasi() {
  const baru   = document.getElementById('passBaru').value;
  const ulang  = document.getElementById('passUlang').value;
  const lbl    = document.getElementById('konfirmasiLabel');
  if (!ulang) { lbl.textContent = ''; return; }
  if (baru === ulang) {
    lbl.textContent = '✓ Password cocok';
    lbl.style.color = '#059669';
  } else {
    lbl.textContent = '✗ Password tidak cocok';
    lbl.style.color = '#ef4444';
  }
}

/* Preview foto sebelum diupload */
function previewFoto(input) {
  const preview  = document.getElementById('preview-foto');
  const btnUpload = document.getElementById('btnUpload');
  const zone     = document.getElementById('uploadZone');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src     = e.target.result;
      preview.style.display = 'block';
      zone.querySelector('.uz-icon').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
    btnUpload.disabled = false;
    btnUpload.style.opacity = '1';
  }
}

/* Tutup modal foto jika klik di luar */
document.getElementById('modalFoto').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});

/* Validasi form password sebelum submit */
document.getElementById('formPassword').addEventListener('submit', function(e) {
  const baru  = document.getElementById('passBaru').value;
  const ulang = document.getElementById('passUlang').value;
  if (baru !== ulang) {
    e.preventDefault();
    alert('Konfirmasi password tidak cocok!');
  }
});
</script>

<?php include "template/footer.php"; ?>