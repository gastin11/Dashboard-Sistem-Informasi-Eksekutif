# 📊 Dashboard Sistem Informasi Eksekutif (SIE) - Global Superstore

Proyek ini adalah implementasi **Sistem Informasi Eksekutif (SIE)** berbasis web yang dirancang untuk menganalisis data penjualan **Global Superstore**. Tujuannya adalah menyediakan visualisasi yang komprehensif dan alat analisis mendalam bagi para eksekutif untuk mendukung pengambilan keputusan strategis.

## ✨ Fitur Utama

Sistem ini memiliki kapabilitas analisis tingkat tinggi yang meliputi:

* **Dashboard Utama Interaktif**: Menampilkan metrik kinerja kunci (KPI) seperti total penjualan, keuntungan, dan tren waktu nyata.
* **Drill-down Analysis**: Memungkinkan pengguna untuk menelusuri data dari ringkasan (makro) ke detail spesifik (mikro), misalnya, dari wilayah penjualan ke kategori produk tertentu.
* **What-If Analysis (Simulasi Skenario)**: Modul khusus untuk memprediksi dampak perubahan variabel tertentu (misalnya, diskon, biaya pengiriman) terhadap metrik keuntungan di masa depan.
* **API Internal**: Menggunakan `whatif_api.php` dan `drilldown_api.php` untuk memproses perhitungan data yang kompleks secara asinkron dan efisien.
* **Visualisasi Data Modern**: Penggunaan grafik dan *chart* yang mudah dipahami untuk representasi data yang efektif.

## 🛠️ Teknologi dan Dependensi

| Kategori | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Backend** | PHP | Bahasa pemograman sisi server untuk logika dan pemrosesan data. |
| **Database** | MySQL | Digunakan untuk menyimpan data penjualan Global Superstore. |
| **Server** | Apache (XAMPP/WAMP) | Lingkungan server lokal yang diperlukan untuk menjalankan aplikasi PHP. |
| **Frontend** | HTML, CSS, JavaScript | Digunakan untuk antarmuka pengguna dan interaktivitas. |
| **API** | RESTful API Internal | Untuk komunikasi data yang cepat antara frontend dan backend. |

## ⚙️ Panduan Instalasi dan Konfigurasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda.

### 1. Prasyarat

Pastikan Anda telah menginstal salah satu paket server lokal berikut:
* **XAMPP**
* **WAMP**
* **Laragon**

### 2. Persiapan Database

1.  Buka *tool* pengelolaan database Anda (misalnya, phpMyAdmin).
2.  Buat database baru dengan nama yang disarankan: `global_superstore`.
3.  Impor skema dan data dari file **`DB/global_superstore.sql`** ke dalam database yang baru dibuat tersebut.

### 3. Konfigurasi Aplikasi

1.  Salin seluruh folder proyek `Dashboard_SIE_Global_Superstore` ke direktori *root* web server Anda (misalnya, `htdocs` untuk XAMPP).
2.  Buka file koneksi database: `Dashboard_SIE_Global_Superstore/config.php`.
3.  Sesuaikan kredensial koneksi database (username, password, dan nama database) sesuai dengan konfigurasi server lokal Anda:

    ```php
    <?php
    $servername = "localhost";
    $username = "root"; // Ganti jika berbeda
    $password = "";     // Ganti jika berbeda
    $dbname = "global_superstore"; 
    // ... kode koneksi lainnya
    ?>
    ```

### 4. Menjalankan Aplikasi

Akses aplikasi melalui peramban web (browser) dengan URL berikut (sesuaikan jika Anda mengganti nama folder):
