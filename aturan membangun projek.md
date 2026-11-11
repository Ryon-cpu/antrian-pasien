# Aturan Membangun Projek: Web Antrian Pasien

> Dokumen ini adalah acuan utama pembangunan projek. Semua keputusan teknis dan fitur harus mengikuti aturan di bawah. Bagian bertanda **[ISI]** wajib dilengkapi sebelum pengembangan dimulai.

---

## 1. Identitas Projek

| Item | Keterangan |
|---|---|
| Nama web | **Ditentukan belakangan.** Semua teks nama web wajib diambil dari satu konfigurasi (`APP_NAME` di `.env` / file `config`) agar mudah diganti |
| Tujuan | Mengelola antrian pasien secara digital dan memberi notifikasi giliran kepada pendamping pasien |
| Pengguna utama | Petugas antrian, pendamping pasien (terutama untuk pasien lanjut usia) |
| Pengunjung umum | Siapa pun yang membuka landing page atau memindai QR code |
| Platform | Web responsif (prioritas **mobile-first**, karena pendamping memakai HP) |

---

## 2. Role dan Hak Akses

Ada **2 role**. **Petugas antrian** masuk dengan akun (username dan password). **Pendamping pasien tidak perlu akun, OTP, PIN, maupun email**: cukup scan QR code, lalu langsung mengisi form pendaftaran (lihat 2.3). Pengunjung umum bebas melihat landing page.

### 2.1 Role: Petugas Antrian

**Tanggung jawab:** menjaga dan mengatur jalannya nomor antrian.

| Hak akses | Keterangan |
|---|---|
| Melihat daftar antrian | Semua pasien yang terdaftar hari ini, lengkap dengan nomor dan status |
| Memanggil pasien berikutnya | Memajukan antrian ke nomor selanjutnya |
| Menggeser nomor antrian | Jika pasien pulang lebih dulu padahal antrian masih lama, nomor pasien tersebut dipindahkan ke posisi lebih bawah (lihat aturan 4.2) |
| Menandai pasien selesai | Mengakhiri pemeriksaan dan memicu jendela ucapan terima kasih |
| Melihat riwayat antrian | Daftar antrian hari sebelumnya (opsional) |

### 2.2 Role: Pendamping Pasien

**Tanggung jawab:** mendaftarkan dan mendampingi pasien, terutama pasien lanjut usia.

| Hak akses | Keterangan |
|---|---|
| Mendaftarkan pasien | Mengisi form identitas pasien (lihat bagian 4.1) |
| Melihat nomor antrian pasien | Nomor dan status antrian milik pasien yang ia daftarkan |
| Batas pendaftaran | Hanya boleh punya **1 antrian aktif**; baru bisa mendaftarkan pasien lagi setelah antrian sebelumnya **Selesai** (lihat 4.1) |
| Menerima notifikasi giliran | Jendela "sekarang giliran anda" muncul di HP |
| Menerima ucapan penutup | Jendela terima kasih setelah pemeriksaan selesai |

### 2.3 Aturan Akses Pendamping (Tanpa Login, Lewat QR Code)

Pendamping memakai **HP saja** dan **tidak ada OTP, PIN, password, maupun email**. Alurnya:

1. Pendamping **scan QR code** yang tersedia di tempat pendaftaran.
2. HP langsung membuka web (bertipe **landing page**), dan **form pendaftaran muncul di bagian paling atas**.
3. Pendamping mengisi form (Nama, Usia, Alamat, Keterangan Sakit) lalu menekan kirim.
4. Setelah form terkirim, server membuat **token sesi anonim** yang disimpan di browser HP (cookie). Token inilah yang mengikat HP tersebut dengan antrian pasien.
5. Dengan token itu, HP pendamping otomatis menampilkan nomor antrian, status, serta jendela "sekarang giliran anda" dan ucapan terima kasih, tanpa langkah login apa pun.
6. Token berlaku sampai antrian **Selesai** atau **Dibatalkan**, lalu dihapus/kedaluwarsa.
7. Tidak perlu menginstal aplikasi; semuanya berjalan di browser HP.

**Catatan penting untuk pengembangan:**
- Karena tanpa akun, identitas pendamping hanyalah token di HP tersebut. Jika browser ditutup atau data browser dihapus, halaman antrian bisa hilang. Mitigasinya: simpan token di `localStorage` dan cookie, serta sediakan **link/QR hasil pendaftaran** berisi kode unik (contoh: `/antrian/KODE-UNIK`) yang bisa dibuka ulang di HP yang sama.
- Kode unik harus **acak dan sulit ditebak** (minimal 16 karakter), bukan nomor urut biasa.

### 2.4 Aturan Keamanan Role

1. Setiap role hanya boleh membuka halaman yang menjadi haknya (*role-based access control*).
2. Pendamping **tidak boleh** melihat atau mengubah antrian pasien milik pendamping lain; akses hanya lewat token/kode unik miliknya.
3. Hanya petugas antrian yang boleh menggeser, memanggil, dan menyelesaikan antrian.
4. Password petugas disimpan dalam bentuk hash (`password_hash()` dengan Argon2id atau bcrypt), tidak pernah disimpan polos.
5. Gunakan proteksi CSRF pada setiap form dan session yang aman (`HttpOnly`, `Secure`, `SameSite`).

---

## 3. Struktur Halaman Web

### 3.1 Halaman Publik (tanpa login)

| No | Halaman / Bagian | Isi |
|---|---|---|
| 1 | **Landing page** | Halaman pembuka dengan identitas klinik/praktik, ajakan mendaftar, dan navigasi |
| 2 | **Profil dokter** | Nama lengkap, gelar, spesialisasi, pendidikan, pengalaman, jadwal praktik, foto |
| 3 | **Prestasi dokter** | Gambar presentasi/penghargaan dokter, judul prestasi, tahun, dan keterangan |
| 4 | **Form pendaftaran pemeriksaan** | Lihat aturan 3.2 |

### 3.2 Aturan Scan QR Code (Barcode)

1. Disediakan **QR code** yang dicetak (poster/meja pendaftaran) dan mengarah langsung ke URL web.
2. Setelah dipindai, web terbuka dan **form pendaftaran pemeriksaan muncul di bagian paling atas halaman** (di atas profil dokter dan prestasi), tanpa pengunjung harus mencari menu.
3. **Tidak ada langkah login.** Form pendaftaran langsung bisa diisi begitu halaman terbuka.
4. URL QR sebaiknya memakai parameter khusus (contoh: `/?src=qr`) agar landing page tahu harus menampilkan form di posisi teratas.
5. Ukuran QR code minimal 3 x 3 cm dan diuji pemindaiannya dari jarak dekat di berbagai HP.

---

## 4. Aturan Fitur

### 4.1 Pendaftaran Pasien (oleh Pendamping)

Digunakan terutama bila pasien lanjut usia.

| Field | Tipe | Wajib | Aturan validasi |
|---|---|---|---|
| Nama | Teks | Ya | 3 sampai 100 karakter, hanya huruf, spasi, titik, tanda petik |
| Usia | Angka | Ya | 0 sampai 120; jika di bawah 60, tampilkan peringatan bahwa fitur ini diutamakan untuk lansia (tetap boleh lanjut) |
| Alamat | Teks panjang | Ya | Maksimal 255 karakter |
| Keterangan sakit | Teks panjang | Ya | Keluhan singkat, maksimal 500 karakter |

**Aturan batas pendaftaran:**
1. Satu pendamping (satu HP/token) **tidak boleh** mendaftarkan pasien baru selama masih punya antrian berstatus **Menunggu** atau **Dipanggil**.
2. Pendaftaran baru baru dibuka kembali setelah antrian sebelumnya berstatus **Selesai** (atau **Dibatalkan** oleh petugas).
3. Validasi ini wajib dilakukan di **server** berdasarkan token sesi, bukan hanya menyembunyikan tombol di tampilan. Jika token ada dan antriannya masih aktif, form diganti dengan tampilan nomor antrian yang sedang berjalan.
4. Jika pendamping mencoba mendaftar lagi, tampilkan pesan: *"Pasien Anda masih dalam antrian. Pendaftaran baru bisa dilakukan setelah antrian selesai."*

Alur:
1. Pendamping scan QR, halaman terbuka, lalu langsung mengisi form (tanpa login).
2. Sistem memvalidasi data di sisi klien dan **wajib di sisi server**.
3. Sistem menghasilkan nomor antrian otomatis (urut per hari, reset tiap hari).
4. Pendamping melihat halaman konfirmasi: nomor antrian, estimasi posisi, dan status **Menunggu**.

### 4.2 Pengaturan dan Penggeseran Antrian (oleh Petugas Antrian)

**Skenario:** pasien pulang lebih dulu karena antrian masih lama, sehingga nomornya perlu digeser ke bawah.

Aturan:
1. Petugas memilih pasien yang akan digeser, lalu menentukan posisi tujuan (di bawah beberapa nomor, atau paling akhir).
2. Sistem **menghitung ulang urutan**; nomor pasien lain tidak boleh hilang atau ganda.
3. Pasien yang digeser statusnya tetap **Menunggu** dan tercatat dalam log (siapa yang menggeser, kapan, dari posisi berapa ke posisi berapa).
4. Pendamping pasien yang digeser **menerima pemberitahuan** bahwa posisi antrian berubah.
5. Pasien yang digeser tidak boleh dilewati secara permanen; jika kembali, ia tetap dilayani sesuai posisi barunya.
6. Penggeseran dan pemanggilan harus aman dari bentrokan data (gunakan *database transaction*).

> **Catatan keputusan [ISI]:** apakah pasien yang pulang boleh digeser lebih dari sekali? Apakah ada batas maksimal penggeseran?

### 4.3 Notifikasi Giliran (ke HP Pendamping)

1. Saat nomor pasien **tiba gilirannya** (dipanggil petugas), HP pendamping langsung menampilkan **jendela (modal/pop-up)** berisi:
   > **"Sekarang giliran Anda"**
   > beserta nama pasien dan nomor antrian.
2. Jendela muncul **otomatis tanpa perlu refresh** halaman.
3. Jendela harus besar, kontras tinggi, dan mudah dibaca (pendamping/pasien lansia), disertai getaran dan/atau bunyi bila browser mengizinkan.
4. Jendela tidak hilang sendiri sampai pendamping menekan tombol **"Mengerti"**.
5. Jika HP pendamping sedang tidak membuka web, gunakan **Web Push Notification** sebagai cadangan (opsional, tahap lanjutan).
6. **Tidak ada layar TV/monitor ruang tunggu.** Satu-satunya penanda giliran adalah jendela notifikasi di HP pendamping.

### 4.4 Ucapan Penutup (setelah Pemeriksaan Selesai)

1. Saat petugas menandai pasien **selesai**, HP pendamping menampilkan jendela:
   > **"Terima kasih karena sudah berkunjung, semoga pasien cepat sembuh."**
2. Setelah ditutup, status antrian berubah menjadi **Selesai** dan pendamping diarahkan ke halaman utama.

### 4.5 Status Antrian

| Status | Arti |
|---|---|
| Menunggu | Sudah terdaftar, belum dipanggil |
| Dipanggil | Giliran tiba, notifikasi terkirim |
| Selesai | Pemeriksaan selesai, ucapan terima kasih terkirim |
| Dibatalkan | Pasien pulang dan tidak kembali, atau dibatalkan petugas |

Perubahan status hanya boleh mengikuti urutan: Menunggu → Dipanggil → Selesai (Dibatalkan dapat terjadi dari Menunggu atau Dipanggil).

---

## 5. Teknologi

### 5.1 Back End

| Aturan | Keterangan |
|---|---|
| Bahasa | **PHP versi stabil terbaru** (periksa php.net sebelum mulai; gunakan versi yang masih mendapat dukungan keamanan aktif) |
| Framework (disarankan) | **Laravel versi terbaru**, karena sudah menyediakan autentikasi, role, validasi, migrasi, dan *broadcasting* real-time |
| Database | MySQL atau MariaDB |
| API | REST API berformat JSON bila front end terpisah dari back end |
| Real-time | WebSocket (Laravel Reverb / Pusher) atau *Server-Sent Events*; cadangan sederhana: *polling* tiap 3 sampai 5 detik |
| Dependensi | Kelola dengan Composer, kunci versi lewat `composer.lock` |

Aturan kode PHP:
1. Aktifkan `declare(strict_types=1);` pada file PHP.
2. Ikuti standar penulisan **PSR-12**.
3. Gunakan *prepared statement* / Eloquent untuk semua query (cegah SQL injection).
4. Escape semua output (cegah XSS).
5. Konfigurasi sensitif (kunci, password database) hanya di file `.env`, jangan masuk ke Git.

### 5.2 Front End

**Aturan utama:** front end **disesuaikan dengan projek**, dan **wajib memakai template JavaScript yang dicari di web**, bukan dibuat dari nol.

Cara memilih:

| Jika memakai | Cari template di web yang berbasis |
|---|---|
| React.js | Template **React.js** (contoh sumber: GitHub, ThemeForest, tema gratis berbasis React) |
| Vue.js | Template **Vue.js** |
| Node.js / Next.js / Nuxt | Template **Next.js / Nuxt / Node.js** sesuai pilihan |
| Blade (bawaan Laravel) | Template HTML/Tailwind/Bootstrap yang dapat dipecah menjadi komponen Blade |

Aturan template:
1. **Framework template harus sama dengan framework yang dipakai projek.** Jangan menempelkan template Vue ke projek React, dan sebaliknya.
2. Pilih template bertema **kesehatan / klinik / dashboard medis** yang sudah mencakup: landing page, halaman profil, galeri/prestasi, form, dan dashboard.
3. Template harus **responsif** dan terawat (periksa tanggal pembaruan terakhir dan jumlah unduhan/bintang).
4. Periksa **lisensi** template (gratis, MIT, atau berbayar) sebelum dipakai.
5. Cek kompatibilitas versi library (React/Vue/Node) dengan versi yang diinstal.
6. Catat **nama, sumber (URL), dan lisensi** template di bagian 10 dokumen ini.
7. Sesuaikan warna dan teks template dengan identitas web; hapus halaman atau komponen yang tidak dipakai.

---

## 6. Desain dan Pengalaman Pengguna

### 6.1 Warna dan Identitas Visual

Tema warna harus **identik dengan puskesmas, rumah sakit besar, dan klinik** agar langsung terasa sebagai layanan kesehatan.

| Peran warna | Arah pilihan |
|---|---|
| Warna utama | Biru medis atau hijau kesehatan (contoh: biru `#0B6EBD`, hijau `#1E9E6A`) |
| Latar | Putih dan abu sangat muda, kesan bersih dan steril |
| Aksen | Satu warna aksen saja (misalnya biru muda atau hijau muda) |
| Status | Menunggu = kuning/oranye, Dipanggil = biru, Selesai = hijau, Dibatalkan = merah/abu |

Aturan:
1. Seluruh warna didefinisikan di **satu tempat** (CSS variables / file tema), bukan ditulis berulang di tiap halaman.
2. Pastikan kontras teks dan latar memenuhi standar keterbacaan (WCAG AA).
3. Hindari warna menyolok yang tidak lazim untuk layanan kesehatan.

### 6.2 Logo dan Nama (Mudah Diganti)

Nama web dan logo **ditentukan belakangan**, sehingga harus bisa diganti tanpa mengubah kode.

1. Semua logo ditaruh di folder khusus, contoh `public/images/brand/`.
2. Nama file logo **tetap dan seragam**: `logo.png` (logo utama), `logo-white.png` (versi untuk latar gelap), dan `favicon.png`.
3. Mengganti logo cukup dengan **menimpa file** dengan nama yang sama; tidak boleh ada perubahan kode.
4. Selama logo belum ada, pasang logo sementara (*placeholder*) bergambar ikon medis sederhana.
5. Nama web diambil dari satu konfigurasi (`APP_NAME`) dan dipakai di judul tab, header, footer, dan teks notifikasi.

### 6.3 Pengalaman Pengguna

1. **Ramah lansia:** ukuran huruf minimal 16 px, tombol besar, kontras tinggi, kalimat singkat dan jelas.
2. **Mobile-first:** semua halaman diuji di layar HP kecil (lebar 360 px).
3. Form pendaftaran harus singkat dan tidak membingungkan; tampilkan pesan error yang jelas dalam Bahasa Indonesia.
4. Bahasa antarmuka: **Bahasa Indonesia**.
5. Tampilan antrian petugas berupa tabel/daftar yang mudah dipindai, dengan tombol aksi jelas (Panggil, Geser, Selesai).
6. Hindari animasi berlebihan yang memperlambat HP lama.

---

## 7. Rancangan Database (Garis Besar)

| Tabel | Kolom utama |
|---|---|
| `users` | id, nama, username, password (hash), role (`petugas`); hanya untuk petugas antrian |
| `patients` | id, nama, usia, alamat, keterangan_sakit, token_sesi (acak, unik), kode_unik |
| `queues` | id, patient_id, doctor_id (boleh kosong), nomor, posisi, tanggal, status, dipanggil_pada, selesai_pada |
| `queue_logs` | id, queue_id, aksi, dari_posisi, ke_posisi, oleh_user_id, waktu |
| `doctors` | id, nama_lengkap, gelar, spesialisasi, pendidikan, pengalaman, foto, jadwal |
| `achievements` | id, doctor_id, judul, tahun, keterangan, gambar |

Aturan: gunakan migrasi (*migration*) untuk semua perubahan skema; relasi memakai foreign key.

---

## 8. Aturan Keamanan dan Privasi Data

Data pasien (nama, alamat, keluhan sakit) adalah **data pribadi sensitif**.

1. Wajib memakai **HTTPS**.
2. Hanya petugas antrian dan pendamping terkait yang boleh melihat data pasien.
3. Layar publik (jika ada) hanya menampilkan **nomor antrian**, bukan nama atau keluhan.
4. Batasi percobaan login (*rate limiting*) dan validasi semua input di server.
5. Cadangkan (*backup*) database secara berkala.
6. Hapus atau anonimkan data lama sesuai kebijakan retensi **[ISI]**.

---

## 9. Alur Kerja Pengembangan

1. **Persiapan:** tentukan nama web, pilih framework front end, cari dan pilih template.
2. **Setup:** buat repositori Git, instal PHP/Composer, inisialisasi back end dan front end.
3. **Database:** buat migrasi dan data awal (akun petugas, data dokter).
4. **Autentikasi dan role:** login, logout, pembatasan akses per role.
5. **Fitur inti:** pendaftaran pasien → pembuatan nomor antrian → panggil, geser, selesai.
6. **Real-time dan notifikasi:** jendela "sekarang giliran anda" dan ucapan terima kasih.
7. **Halaman publik:** landing page, profil dokter, prestasi, dan form di bagian teratas untuk akses QR.
8. **QR code:** buat dan uji pemindaian.
9. **Pengujian:** uji tiap role, uji penggeseran antrian, uji di berbagai HP.
10. **Peluncuran:** deploy ke hosting dengan HTTPS dan pantau error.

**Aturan Git:**
- Branch: `main` (stabil), `dev` (pengembangan), `fitur/nama-fitur`.
- Pesan commit jelas, contoh: `feat: tambah fitur geser antrian`.

---

## 10. Catatan Template dan Keputusan

| Item | Isi |
|---|---|
| Framework front end terpilih | Blade + Tailwind CSS v4 |
| Nama template | TailAdmin Free Tailwind Dashboard Template (v2.4.0), disesuaikan untuk antrean klinik |
| Sumber (URL) template | [TailAdmin](https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template) |
| Lisensi template | MIT |
| Versi PHP yang dipakai | PHP 8.4.12 |
| Framework back end | Laravel 13.17 |

---

## 11. Kriteria Selesai (Checklist)

- [ ] Petugas dapat login; pendamping langsung memakai web lewat QR tanpa akun
- [ ] Pendamping dapat mendaftarkan pasien (Nama, Usia, Alamat, Keterangan Sakit)
- [ ] Pendamping tidak bisa mendaftar lagi sebelum antrian sebelumnya selesai (dicek di server)
- [ ] Pendamping masuk lewat scan QR tanpa login, OTP, PIN, atau email
- [ ] Nomor antrian terbentuk otomatis dan berurutan
- [ ] Petugas dapat memanggil, menggeser, dan menyelesaikan antrian
- [ ] Penggeseran antrian tercatat di log dan tidak menimbulkan nomor ganda
- [ ] Jendela "sekarang giliran anda" muncul otomatis di HP pendamping
- [ ] Jendela terima kasih muncul setelah pemeriksaan selesai
- [ ] Landing page, profil dokter, dan prestasi dokter tampil lengkap
- [ ] Scan QR langsung membuka web dengan form pendaftaran di bagian paling atas
- [ ] Front end memakai template JS dari web yang sesuai framework
- [ ] Warna bertema kesehatan dan terpusat di satu file tema
- [ ] Logo dan nama web bisa diganti hanya dengan mengganti file/konfigurasi
- [ ] Semua halaman nyaman dipakai di HP dan ramah lansia
- [ ] Keamanan dasar (HTTPS, hash password, validasi, CSRF) terpenuhi

---

## 12. Keputusan yang Sudah Ditetapkan dan yang Masih Terbuka

### 12.1 Sudah Diputuskan

| No | Topik | Keputusan |
|---|---|---|
| 1 | Warna | Identik dengan puskesmas, rumah sakit besar, dan klinik (lihat 6.1) |
| 2 | Nama web | Ditentukan belakangan, diambil dari satu konfigurasi |
| 3 | Logo | Cukup ganti nama file (lihat 6.2) |
| 4 | Akses pendamping | HP saja, **tanpa OTP, PIN, password, atau email**; masuk lewat scan barcode ke landing page dan langsung ke form pendaftaran (lihat 2.3) |
| 5 | Pendaftaran berulang | **Tidak boleh** mendaftarkan pasien lagi kecuali antrian sebelumnya sudah selesai (lihat 4.1) |
| 6 | Layar TV ruang tunggu | **Tidak perlu**; cukup jendela notifikasi bahwa antrian sudah sampai (lihat 4.3) |

### 12.2 Masih Terbuka

| No | Topik | Catatan |
|---|---|---|
| 1 | Antrian per dokter/poli | **Belum dipikirkan.** Tujuan web adalah mengontrol pasien yang sedang sakit atau periksa kesehatan. Untuk sementara dibuat **satu antrian tunggal**. Tabel `queues` disiapkan dengan kolom `doctor_id` yang boleh kosong (*nullable*) agar nanti mudah dikembangkan menjadi antrian per dokter tanpa merombak database |
| 2 | Lama penyimpanan data | Kebijakan retensi data pasien (lihat bagian 8) |
