# Mengunggah NetCalc ke GitHub

Target awal proyek ini adalah repository bernama **netcalc** dengan visibilitas **Private**.

## File yang diunggah

Unggah source pada `app`, `bootstrap`, `config`, `database/migrations`, `database/factories`, `database/seeders`, `public`, `resources`, `routes`, dan `tests`. Sertakan juga `docs`, `.github`, `.env.example`, `.gitignore`, `.gitattributes`, `.editorconfig`, `artisan`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `phpunit.xml`, `vite.config.js`, `README.md`, dan `LICENSE`.

Pertahankan file `.gitignore` di dalam direktori runtime agar folder yang dibutuhkan Laravel tetap tersedia setelah clone.

Jangan menambahkan `.env` asli, database lokal, credential, `vendor`, `node_modules`, `public/build`, `public/hot`, log, session, cache, atau artefak QA. Aturan pengecualiannya sudah disiapkan di `.gitignore`.

## Publikasi melalui Git

Jika proyek sudah memiliki commit pada branch `main`, mulai dari langkah membuat repository dan menghubungkan remote. Untuk folder yang belum menjadi repository:

```bash
git init -b main
git add .
git status --short
git commit -m "Prepare NetCalc for GitHub"
```

Git memerlukan identitas penulis commit. Gunakan nama dan alamat email GitHub Anda sendiri atau email privat dari pengaturan GitHub; jangan memasukkan token sebagai email atau menaruhnya di source.

### Dengan GitHub CLI

Setelah Git dan GitHub CLI tersedia, masuk menggunakan browser:

```bash
gh auth login
gh repo create netcalc --private --source=. --remote=origin --push
```

Jalankan pembuatan repository hanya sekali. Jika repository `netcalc` sudah ada, gunakan URL repository tersebut sebagai `origin` dan push ke repository yang sama.

### Dengan website GitHub

1. Buat repository **netcalc** dengan pilihan **Private**.
2. Biarkan repository kosong: jangan buat README, lisensi, atau `.gitignore` tambahan dari website karena file tersebut sudah tersedia di proyek.
3. Hubungkan URL repository yang dibuat:

```bash
git remote add origin https://github.com/USERNAME/netcalc.git
git push -u origin main
```

Ganti `USERNAME` dengan akun pemilik repository. Jika remote `origin` sudah ada, periksa `git remote -v` sebelum mengubahnya.

## Setelah push

- Buka repository dan pastikan visibilitasnya **Private**.
- Periksa **Actions → Tests** untuk hasil tes PHP, JavaScript, dan build. Pada repository privat, ketersediaan serta pemakaian Actions mengikuti pengaturan akun atau organisasi.
- Periksa README dan screenshot pada halaman repository.
- Setiap instalasi baru mengikuti [README](../README.md); tidak memerlukan salinan `.env`, database, `vendor`, atau `node_modules` dari mesin pembuat.

Upload ke GitHub tidak otomatis menyediakan hosting PHP. Publikasi aplikasi ke server merupakan langkah terpisah.
