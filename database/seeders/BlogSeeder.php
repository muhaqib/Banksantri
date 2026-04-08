<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blogs = [
            [
                'title' => 'Pentingnya Pendidikan Karakter di Pondok Pesantren',
                'slug' => 'pentingnya-pendidikan-karakter-di-pondok-pesantren',
                'excerpt' => 'Pendidikan karakter menjadi fondasi utama dalam membentuk generasi yang berakhlakul karimah di lingkungan pondok pesantren.',
                'content' => '<p>Pendidikan karakter menjadi fondasi utama dalam membentuk generasi yang berakhlakul karimah di lingkungan pondok pesantren. Di Pondok Pesantren Mambaul Hikmah, kami percaya bahwa pendidikan tidak hanya sebatas transfer ilmu pengetahuan, tetapi juga pembentukan karakter yang kuat.</p>

<h2>Apa itu Pendidikan Karakter?</h2>
<p>Pendidikan karakter adalah proses pendidikan yang mengutamakan penanaman nilai-nilai moral, akhlak, dan budi pekerti kepada peserta didik. Tujuannya adalah membentuk manusia yang tidak hanya cerdas secara intelektual, tetapi juga memiliki akhlak yang mulia.</p>

<h2>Implementasi di Pondok Pesantren</h2>
<p>Di pondok pesantren, pendidikan karakter diimplementasikan melalui berbagai kegiatan:</p>
<ul>
<li>Pembiasaan ibadah harian (sholat berjamaah, dzikir, doa)</li>
<li>Pengajian kitab kuning</li>
<li>Program hafalan Al-Quran</li>
<li>Kegiatan sosial dan kemasyarakatan</li>
<li>Pembentukan adab dan akhlak在日常 kehidupan</li>
</ul>

<h2>Hasil yang Diharapkan</h2>
<p>Dengan pendidikan karakter yang baik, diharapkan para santri dapat menjadi generasi yang:</p>
<ul>
<li>Bertaqwa kepada Allah SWT</li>
<li>Berakhlakul karimah</li>
<li>Memiliki ilmu pengetahuan yang mumpuni</li>
<li>Bermanfaat bagi masyarakat</li>
</ul>

<p>Kami berkomitmen untuk terus meningkatkan kualitas pendidikan karakter di Pondok Pesantren Mambaul Hikmah demi masa depan generasi yang lebih baik.</p>',
                'category' => 'Pendidikan',
                'author' => 'Admin Pondok',
                'is_published' => true,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Kegiatan Hafalan Al-Quran Santri',
                'slug' => 'kegiatan-hafalan-al-quran-santri',
                'excerpt' => 'Program tahfidz Al-Quran menjadi unggulan di Pondok Pesantren Mambaul Hikmah dengan metode pembelajaran yang efektif.',
                'content' => '<p>Program tahfidz Al-Quran menjadi unggulan di Pondok Pesantren Mambaul Hikmah. Kami menyadari bahwa menghafal Al-Quran adalah kemuliaan yang besar bagi setiap muslim.</p>

<h2>Metode Pembelajaran</h2>
<p>Kami menggunakan metode yang telah terbukti efektif dalam membantu santri menghafal Al-Quran:</p>
<ul>
<li>Metode tikrar (pengulangan)</li>
<li>Murajaah (review berkala)</li>
<li>Tasmi (setoran hafalan)</li>
<li>Bimbingan ustadz/ustadzah yang berpengalaman</li>
</ul>

<h2>Target Hafalan</h2>
<p>Setiap santri memiliki target hafalan yang disesuaikan dengan kemampuan dan jenjang pendidikannya. Target minimal adalah hafalan 5 juz untuk santri MA.</p>

<p>Kami bangga dengan pencapaian para santri yang telah berhasil menghafal Al-Quran dengan baik. Semoga Allah memudahkan urusan mereka dalam menghafal dan mengamalkan Al-Quran.</p>',
                'category' => 'Kegiatan',
                'author' => 'Ustadz Ahmad',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'title' => 'Prestasi Santri dalam Musabaqah Tilawatil Quran',
                'slug' => 'prestasi-santri-dalam-mtq',
                'excerpt' => 'Santri Pondok Pesantren Mambaul Hikmah meraih juara dalam berbagai cabang lomba MTQ tingkat kabupaten.',
                'content' => '<p>Alhamdulillah, santri Pondok Pesantren Mambaul Hikmah berhasil meraih prestasi yang membanggakan dalam Musabaqah Tilawatil Quran (MTQ) tingkat kabupaten yang lalu.</p>

<h2>Prestasi yang Diraih</h2>
<p>Berikut adalah beberapa prestasi yang berhasil diraih:</p>
<ul>
<li>Juara 1 Cabang Lomba Tilawatil Quran Putra</li>
<li>Juara 2 Cabang Lomba Hifdzil Quran Putri</li>
<li>Juara 3 Cabang Lomba Syarhil Quran</li>
</ul>

<h2>Persiapan yang Matang</h2>
<p>Prestasi ini tidak datang begitu saja. Para santri telah melakukan persiapan yang matang selama berbulan-bulan di bawah bimbingan ustadz dan ustadzah yang berpengalaman.</p>

<p>Kami bangga dengan pencapaian mereka dan berharap prestasi ini dapat memotivasi santri-santri lainnya untuk terus meningkatkan kemampuan dalam membaca, menghafal, dan mengamalkan Al-Quran.</p>',
                'category' => 'Berita',
                'author' => 'Admin Pondok',
                'is_published' => true,
                'published_at' => now()->subDays(7),
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'title' => 'Pendaftaran Santri Baru Tahun Ajaran 2026/2027',
                'slug' => 'pendaftaran-santri-baru-2026-2027',
                'excerpt' => 'Pondok Pesantren Mambaul Hikmah membuka pendaftaran santri baru untuk tahun ajaran 2026/2027.',
                'content' => '<p>Dengan ini kami sampaikan bahwa Pondok Pesantren Mambaul Hikmah telah membuka pendaftaran santri baru untuk tahun ajaran 2026/2027.</p>

<h2>Jenjang Pendidikan</h2>
<p>Kami membuka pendaftaran untuk jenjang:</p>
<ul>
<li>RA (Raudhatul Athfal)</li>
<li>MI (Madrasah Ibtidaiyah)</li>
<li>MTs (Madrasah Tsanawiyah)</li>
<li>MA (Madrasah Aliyah)</li>
<li>STIT (Sekolah Tinggi Ilmu Tarbiyah)</li>
</ul>

<h2>Persyaratan</h2>
<ul>
<li>Mengisi formulir pendaftaran</li>
<li>Fotokopi Akta Kelahiran</li>
<li>Fotokopi Kartu Keluarga</li>
<li>Pas foto 3x4 (2 lembar)</li>
<li>Surat keterangan sehat</li>
</ul>

<h2>Waktu Pendaftaran</h2>
<p>Pendaftaran dibuka mulai tanggal 1 April 2026 sampai dengan 30 Juni 2026.</p>

<p>Untuk informasi lebih lanjut, silakan menghubungi sekretariat pondok atau mendaftar secara online melalui website ini.</p>',
                'category' => 'Pengumuman',
                'author' => 'Panitia PPDB',
                'is_published' => true,
                'published_at' => now()->subDays(14),
                'created_at' => now()->subDays(14),
                'updated_at' => now()->subDays(14),
            ],
        ];

        foreach ($blogs as $blog) {
            Blog::create($blog);
        }

        $this->command->info('Sample blog posts created successfully!');
    }
}
