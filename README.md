# 💰 FinTrack Premium - Pencatat Keuangan Modern

FinTrack Premium adalah aplikasi web manajemen keuangan personal yang dibangun menggunakan **Django (Python)** dan dipercantik dengan **Tailwind CSS**. Aplikasi ini memungkinkan pengguna untuk memantau saldo, mencatat pemasukan dan pengeluaran secara dinamis berdasarkan bulan dan tahun, serta melihat visualisasi proporsi pengeluaran melalui grafik donut interaktif.

## ✨ Fitur Utama
- **Dashboard Keuangan Estetik**: Menggunakan font *Plus Jakarta Sans* dan efek *glassmorphism* modern.
- **Filter Dinamis**: Menyaring sirkulasi kas berdasarkan Bulan dan Tahun secara *real-time*.
- **Analisis Grafik Interaktif**: Visualisasi kategori pengeluaran menggunakan *Chart.js*.
- **Unduh Laporan**: Fitur ekspor data transaksi ke dalam format CSV/Excel sesuai periode yang dipilih.
- **Sistem Autentikasi Pengguna**: Registrasi, Login, dan Logout yang aman terhubung ke database.

## 🛠️ Teknologi yang Digunakan
- **Backend:** Django 5.x (Python)
- **Frontend:** Tailwind CSS, Chart.js
- **Database:** PostgreSQL (Supabase Cloud) / SQLite

## 🚀 Cara Menjalankan di Lokal
1. Clone repositori ini
2. Install library yang dibutuhkan: `pip install django`
3. Jalankan migrasi database: `python manage.py migrate`
4. Jalankan server: `python manage.py runserver`
