# Implementasi Algoritma C5.0 untuk Klasifikasi Status Stunting
Proyek ini merupakan implementasi algoritma C5.0 menggunakan PHP murni untuk melakukan klasifikasi status stunting berdasarkan atribut-atribut yang relevan. Sistem ini dikembangkan sebagai bagian dari Tugas Akhir dengan
tujuan menerapkan metode data mining dalam mendukung analisis permasalahan kesehatan masyarakat, khususnya dalam identifikasi status stunting.
Algoritma C5.0 digunakan untuk membangun model klasifikasi berbasis pohon keputusan (decision tree) yang menghasilkan aturan keputusan berdasarkan data yang diproses.

## Latar Belakang
Stunting merupakan kondisi gagal tumbuh pada anak akibat kekurangan gizi kronis dalam jangka panjang. Permasalahan ini berdampak pada perkembangan fisik maupun kognitif anak. Oleh karena itu, diperlukan pendekatan analitis
yang mampu membantu proses identifikasi status stunting secara sistematis.
Penerapan teknik klasifikasi dalam data mining, khususnya menggunakan algoritma C5.0, diharapkan mampu menghasilkan model prediksi yang dapat membantu dalam proses pengambilan keputusan berbasis data.

## Metodologi
Tahapan penelitian yang dilakukan meliputi:
1. Pengumpulan dan pemahaman dataset
2. Preprocessing data (pembersihan dan transformasi)
3. Perhitungan entropy dan gain ratio
4. Pembentukan pohon keputusan menggunakan algoritma C5.0
5. Pengujian model menggunakan data testing
6. Evaluasi performa menggunakan confusion matrix

## Dataset
Dataset yang digunakan terdiri dari atribut-atribut yang berkaitan dengan kondisi anak, antara lain:
* Usia
* Berat badan
* Tinggi badan
* Jenis kelamin
* Variabel pendukung lainnya
Data telah melalui tahap validasi dan praproses sebelum digunakan dalam pembentukan model klasifikasi.

## Hasil dan Evaluasi
Model klasifikasi yang dibangun menggunakan algoritma C5.0 menghasilkan:
**Akurasi sebesar 71,33%**
Evaluasi dilakukan menggunakan confusion matrix untuk mengukur tingkat ketepatan model dalam mengklasifikasikan data uji.

## Implementasi Sistem
Sistem dikembangkan menggunakan:
* PHP murni (tanpa framework)
* HTML dan CSS untuk antarmuka pengguna
* Struktur modular untuk proses perhitungan algoritma
Seluruh proses perhitungan entropy, gain, gain ratio, serta pembentukan pohon keputusan diimplementasikan secara manual dalam kode PHP.

## Struktur Direktori
```
├── data/               # Dataset
├── proses/             # Logika perhitungan algoritma C5.0
├── hasil/              # Output dan hasil klasifikasi
├── index.php           # Halaman utama sistem
├── README.md
```

## Cara Menjalankan
1. Pastikan XAMPP atau Laragon telah terinstal.
2. Pindahkan folder project ke dalam direktori `htdocs` (XAMPP) atau `www` (Laragon).
3. Jalankan Apache melalui control panel.
4. Buka browser dan akses:

```
http://localhost/nama-folder-project
```

## Kontribusi Akademik
Proyek ini disusun sebagai salah satu syarat penyelesaian Tugas Akhir pada program studi terkait. Implementasi ini diharapkan dapat memberikan kontribusi dalam penerapan teknik data mining,
khususnya algoritma C5.0, pada bidang kesehatan masyarakat.

## Penulis
Nama    : MUHARRIR RAJUDDIN
E-Mail  : muharrir011@gmail.com
IG      : muharrir_rajuddin

