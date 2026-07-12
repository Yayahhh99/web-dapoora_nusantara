-- ============================================================
--  DAPOORA NUSANTARA — Database Schema & Seed Data
--  Engine  : MySQL 8.0+
--  Charset : utf8mb4 / utf8mb4_unicode_ci
--  Generated: 2025
-- ============================================================

CREATE DATABASE IF NOT EXISTS dapoora_nusantara
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dapoora_nusantara;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nama          VARCHAR(100)    NOT NULL,
    email         VARCHAR(150)    NOT NULL,
    password      VARCHAR(255)    NOT NULL,
    foto_profil   VARCHAR(255)        NULL DEFAULT NULL,
    status        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_users_email   (email),
    INDEX   idx_users_status     (status),
    INDEX   idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. ADMINS
-- ============================================================
CREATE TABLE admins (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nama          VARCHAR(100)    NOT NULL,
    email         VARCHAR(150)    NOT NULL,
    password      VARCHAR(255)    NOT NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. CATEGORIES
-- ============================================================
CREATE TABLE categories (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nama          VARCHAR(100)    NOT NULL,
    slug          VARCHAR(120)    NOT NULL,
    icon          VARCHAR(255)        NULL DEFAULT NULL,
    deskripsi     TEXT                NULL DEFAULT NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_categories_slug (slug),
    INDEX   idx_categories_nama    (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. RECIPES
-- ============================================================
CREATE TABLE recipes (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    admin_id        INT UNSIGNED    NOT NULL,
    category_id     INT UNSIGNED    NOT NULL,
    judul           VARCHAR(200)    NOT NULL,
    slug            VARCHAR(220)    NOT NULL,
    foto            VARCHAR(255)        NULL DEFAULT NULL,
    deskripsi       TEXT                NULL DEFAULT NULL,
    bahan           LONGTEXT            NULL DEFAULT NULL COMMENT 'JSON array of ingredients',
    langkah         LONGTEXT            NULL DEFAULT NULL COMMENT 'JSON array of steps',
    waktu_memasak   SMALLINT UNSIGNED   NOT NULL DEFAULT 30 COMMENT 'in minutes',
    porsi           TINYINT UNSIGNED    NOT NULL DEFAULT 2,
    kesulitan       ENUM('mudah','sedang','sulit') NOT NULL DEFAULT 'mudah',
    status          ENUM('publish','draft')        NOT NULL DEFAULT 'draft',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_recipes_slug         (slug),
    INDEX   idx_recipes_admin_id        (admin_id),
    INDEX   idx_recipes_category_id     (category_id),
    INDEX   idx_recipes_status          (status),
    INDEX   idx_recipes_kesulitan       (kesulitan),
    INDEX   idx_recipes_created_at      (created_at),
    INDEX   idx_recipes_status_cat      (status, category_id),

    CONSTRAINT fk_recipes_admin
        FOREIGN KEY (admin_id)    REFERENCES admins(id)     ON DELETE RESTRICT  ON UPDATE CASCADE,
    CONSTRAINT fk_recipes_category
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. BOOKMARKS
-- ============================================================
CREATE TABLE bookmarks (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    recipe_id   INT UNSIGNED    NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_bookmarks_user_recipe (user_id, recipe_id),
    INDEX   idx_bookmarks_user_id        (user_id),
    INDEX   idx_bookmarks_recipe_id      (recipe_id),

    CONSTRAINT fk_bookmarks_user
        FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookmarks_recipe
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. COMMENTS
-- ============================================================
CREATE TABLE comments (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    recipe_id   INT UNSIGNED    NOT NULL,
    komentar    TEXT            NOT NULL,
    status      ENUM('approved','pending','spam') NOT NULL DEFAULT 'pending',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX   idx_comments_user_id   (user_id),
    INDEX   idx_comments_recipe_id (recipe_id),
    INDEX   idx_comments_status    (status),
    INDEX   idx_comments_rec_stat  (recipe_id, status),

    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comments_recipe
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. RATINGS
-- ============================================================
CREATE TABLE ratings (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    recipe_id   INT UNSIGNED    NOT NULL,
    nilai       TINYINT UNSIGNED NOT NULL COMMENT '1-5',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_ratings_user_recipe (user_id, recipe_id),
    INDEX   idx_ratings_recipe_id      (recipe_id),
    INDEX   idx_ratings_nilai          (nilai),

    CONSTRAINT fk_ratings_user
        FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ratings_recipe
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_ratings_nilai CHECK (nilai BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ============================================================
--  SEED DATA
-- ============================================================
-- ============================================================

-- ------------------------------------------------------------
-- ADMINS  (password: Admin@123)
-- password_hash('Admin@123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO admins (nama, email, password) VALUES
('Admin Dapoora',   'admin@dapoora.com',   '$2y$12$9eQA8VPaklOJf.Fd9GS4n.hK8ZHnU1OBuJmLHsL6r4VWS3e5XeUEK'),
('Budi Santoso',    'budi@dapoora.com',    '$2y$12$9eQA8VPaklOJf.Fd9GS4n.hK8ZHnU1OBuJmLHsL6r4VWS3e5XeUEK');

-- ------------------------------------------------------------
-- USERS  (password: User@123)
-- password_hash('User@123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO users (nama, email, password, foto_profil, status) VALUES
('Andi Pratama',      'andi@example.com',      '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', 'andi.jpg',      'aktif'),
('Siti Nurhaliza',    'siti@example.com',      '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', 'siti.jpg',      'aktif'),
('Dian Rahayu',       'dian@example.com',      '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', NULL,            'aktif'),
('Rizky Firmansyah',  'rizky@example.com',     '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', 'rizky.jpg',     'aktif'),
('Dewi Lestari',      'dewi@example.com',      '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', NULL,            'aktif'),
('Hendra Kusuma',     'hendra@example.com',    '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', 'hendra.jpg',    'aktif'),
('Nurul Hidayah',     'nurul@example.com',     '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', NULL,            'aktif'),
('Fajar Setiawan',    'fajar@example.com',     '$2y$12$CK3sPkq1wOoYMgcEwI8GNuBVzMJqHVhWzBt0pE1rLjKzT6Pm.aDlG', NULL,            'nonaktif');

-- ------------------------------------------------------------
-- CATEGORIES (8 kategori)
-- ------------------------------------------------------------
INSERT INTO categories (nama, slug, icon, deskripsi) VALUES
('Makanan Berat',   'makanan-berat',   'icons/makanan-berat.svg',   'Hidangan utama yang mengenyangkan seperti nasi, lauk, dan olahan daging.'),
('Makanan Ringan',  'makanan-ringan',  'icons/makanan-ringan.svg',  'Camilan lezat untuk menemani waktu santai atau selingan di antara makan besar.'),
('Minuman',         'minuman',         'icons/minuman.svg',         'Aneka minuman segar tradisional maupun modern khas Nusantara.'),
('Dessert',         'dessert',         'icons/dessert.svg',         'Kue, puding, dan hidangan manis penutup yang memanjakan lidah.'),
('Tradisional',     'tradisional',     'icons/tradisional.svg',     'Resep warisan leluhur dari berbagai penjuru kepulauan Indonesia.'),
('Sehat',           'sehat',           'icons/sehat.svg',           'Menu bergizi tinggi rendah lemak, cocok untuk gaya hidup aktif dan sehat.'),
('Seafood',         'seafood',         'icons/seafood.svg',         'Olahan hasil laut segar: ikan, udang, cumi, kepiting, dan kerang.'),
('Vegetarian',      'vegetarian',      'icons/vegetarian.svg',      'Masakan lezat tanpa daging, berbahan sayuran, tahu, tempe, dan biji-bijian.');

-- ------------------------------------------------------------
-- RECIPES (15 resep)
-- bahan & langkah disimpan sebagai JSON
-- ------------------------------------------------------------
INSERT INTO recipes
  (admin_id, category_id, judul, slug, foto, deskripsi, bahan, langkah, waktu_memasak, porsi, kesulitan, status)
VALUES

-- 1. Rendang Daging Sapi (Makanan Berat / Tradisional → cat 1)
(1, 1,
 'Rendang Daging Sapi',
 'rendang-daging-sapi',
 'rendang-daging-sapi.jpg',
 'Rendang adalah masakan daging sapi berbumbu kaya rempah khas Minangkabau yang dimasak perlahan hingga kering dan berwarna cokelat kehitaman.',
 '["1 kg daging sapi (potong kotak 4 cm)","1,5 liter santan kental","5 lembar daun jeruk","3 batang serai (memarkan)","3 lembar daun salam","2 cm lengkuas (memarkan)","Garam secukupnya","Bumbu halus: 12 cabai merah keriting, 8 bawang merah, 6 bawang putih, 4 cm jahe, 3 cm kunyit, 3 cm lengkuas, 2 sdt ketumbar sangrai"]',
 '["Haluskan semua bahan bumbu halus menggunakan blender atau ulekan.","Masukkan santan, bumbu halus, serai, daun jeruk, daun salam, dan lengkuas ke dalam wajan besar. Aduk rata.","Masukkan daging sapi, masak dengan api sedang sambil terus diaduk hingga santan mendidih.","Kecilkan api, masak terus selama ±3 jam sambil sesekali diaduk hingga kuah menyusut dan bumbu meresap.","Naikkan api, terus aduk hingga rendang kering dan berwarna cokelat tua. Koreksi rasa, sajikan."]',
 210, 6, 'sulit', 'publish'),

-- 2. Nasi Goreng Kampung (Makanan Berat → cat 1)
(1, 1,
 'Nasi Goreng Kampung',
 'nasi-goreng-kampung',
 'nasi-goreng-kampung.jpg',
 'Nasi goreng klasik ala rumahan dengan bumbu sederhana, telur, dan kecap manis yang menggugah selera.',
 '["3 piring nasi putih dingin","2 butir telur","3 siung bawang putih (cincang)","5 bawang merah (iris)","3 cabai rawit (iris)","2 sdm kecap manis","1 sdt garam","1/2 sdt merica","2 sdm minyak goreng","Daun bawang dan bawang goreng untuk taburan"]',
 '["Panaskan minyak di wajan, tumis bawang putih dan bawang merah hingga harum.","Masukkan cabai rawit, aduk sebentar.","Geser bumbu ke pinggir wajan, kocok lepas telur dan buat orak-arik.","Masukkan nasi, aduk rata bersama bumbu dan telur.","Tambahkan kecap manis, garam, dan merica. Aduk hingga merata dan nasi matang sempurna.","Taburi daun bawang dan bawang goreng. Sajikan selagi panas."]',
 30, 2, 'mudah', 'publish'),

-- 3. Soto Ayam Lamongan (Makanan Berat / Tradisional → cat 1)
(1, 1,
 'Soto Ayam Lamongan',
 'soto-ayam-lamongan',
 'soto-ayam-lamongan.jpg',
 'Soto ayam bening khas Lamongan dengan kuah gurih kekuningan, taburan koya, dan pelengkap sate kerang.',
 '["1 ekor ayam kampung (potong 4)","2 liter air","3 batang serai (memarkan)","4 lembar daun jeruk","2 cm jahe (geprek)","3 cm kunyit (bakar, haluskan)","8 bawang merah","5 bawang putih","1 sdt ketumbar","Garam & gula secukupnya","Pelengkap: taoge, bihun, telur rebus, daun bawang, seledri, jeruk nipis, sambal"]',
 '["Rebus ayam dengan air, serai, daun jeruk, dan jahe hingga matang. Angkat ayam, suwir-suwir.","Tumis bumbu halus (bawang merah, bawang putih, kunyit, ketumbar) hingga harum, masukkan ke kaldu.","Masak kaldu dengan api kecil ±20 menit, bumbui dengan garam dan gula.","Siapkan mangkuk: tata bihun, taoge, dan ayam suwir.","Tuang kuah panas, taburi daun bawang, seledri, dan bawang goreng. Sajikan dengan koya dan sambal."]',
 60, 4, 'sedang', 'publish'),

-- 4. Ayam Penyet (Makanan Berat → cat 1)
(1, 1,
 'Ayam Penyet',
 'ayam-penyet',
 'ayam-penyet.jpg',
 'Ayam goreng empuk yang "dipenyet" lalu disajikan dengan sambal bawang super pedas khas Jawa Timur.',
 '["4 potong ayam","1 liter air untuk merebus","3 cm jahe (geprek)","2 batang serai","Minyak untuk menggoreng","Bumbu ungkep: 6 bawang merah, 4 bawang putih, 2 cm kunyit, 1 sdt ketumbar, garam","Sambal: 10 cabai rawit, 5 cabai merah besar, 4 bawang putih, garam, terasi secukupnya"]',
 '["Haluskan bumbu ungkep, lumuri ayam dan diamkan 30 menit.","Rebus ayam bersama bumbu, serai, dan jahe hingga matang dan empuk ±30 menit.","Angkat dan tiriskan ayam, goreng dalam minyak panas hingga kecokelatan.","Ulek kasar semua bahan sambal yang sebelumnya sudah digoreng/bakar.","Penyet ayam di atas cobek berisi sambal. Sajikan dengan lalapan dan nasi hangat."]',
 40, 4, 'sedang', 'publish'),

-- 5. Rawon Daging (Makanan Berat / Tradisional → cat 1)
(2, 1,
 'Rawon Daging Sapi',
 'rawon-daging-sapi',
 'rawon-daging-sapi.jpg',
 'Sup daging hitam khas Jawa Timur dengan kuah kluwek yang kaya rasa dan aroma, disajikan dengan pelengkap lengkap.',
 '["500 g daging sapi sandung lamur","2 liter air","3 biji kluwek (ambil isinya)","8 bawang merah","5 bawang putih","3 cm lengkuas","3 batang serai","2 cm jahe","1 sdt ketumbar","Garam & gula merah secukupnya","Pelengkap: taoge pendek, daun bawang, telur asin, kerupuk, sambal"]',
 '["Rebus daging hingga empuk, potong kotak, sisihkan kaldunya.","Haluskan kluwek bersama bawang merah, bawang putih, jahe, dan ketumbar.","Tumis bumbu halus dengan serai dan lengkuas hingga harum, tuang ke kaldu.","Masukkan kembali potongan daging, masak ±30 menit. Bumbui garam dan gula merah.","Sajikan dalam mangkuk, taburi taoge dan daun bawang. Lengkapi dengan telur asin dan sambal."]',
 90, 5, 'sulit', 'publish'),

-- 6. Gulai Kambing (Makanan Berat → cat 1)
(1, 1,
 'Gulai Kambing',
 'gulai-kambing',
 'gulai-kambing.jpg',
 'Gulai kambing berkuah santan kuning yang gurih dan harum rempah, cocok untuk acara spesial keluarga.',
 '["700 g daging kambing (potong)","800 ml santan","400 ml air","5 lembar daun kari","3 batang serai","2 cm kayu manis","3 buah cengkih","2 buah kapulaga","Bumbu halus: 10 cabai merah, 8 bawang merah, 6 bawang putih, 3 cm kunyit, 3 cm jahe, 2 cm lengkuas, 1 sdt ketumbar, 1/2 sdt jinten"]',
 '["Tumis bumbu halus bersama daun kari, serai, kayu manis, cengkih, dan kapulaga hingga harum.","Masukkan daging kambing, aduk hingga berubah warna.","Tuang air, masak hingga daging setengah empuk.","Tambahkan santan, masak dengan api kecil sambil diaduk agar santan tidak pecah.","Masak hingga daging empuk dan kuah mengental. Koreksi rasa, sajikan dengan nasi putih."]',
 75, 6, 'sedang', 'publish'),

-- 7. Sate Ayam Madura (Makanan Ringan / Tradisional → cat 2)
(1, 2,
 'Sate Ayam Madura',
 'sate-ayam-madura',
 'sate-ayam-madura.jpg',
 'Sate ayam klasik khas Madura dengan bumbu kacang manis gurih dan kecap, dibakar di atas arang.',
 '["500 g dada/paha ayam (potong dadu)","Tusuk sate secukupnya","Kecap manis untuk olesan","Bumbu kacang: 200 g kacang tanah goreng, 5 cabai merah, 4 bawang putih, 3 sdm kecap manis, 1 sdm air jeruk nipis, garam, 150 ml air panas"]',
 '["Tusuk potongan ayam ke tusuk sate, ±4-5 potong per tusuk.","Bakar sate di atas bara arang sambil dioles kecap manis hingga setengah matang.","Haluskan semua bahan bumbu kacang, masak dengan api kecil sambil diaduk hingga mengental.","Sajikan sate dengan bumbu kacang di atasnya, dilengkapi lontong, irisan bawang merah, dan kecap."]',
 45, 4, 'mudah', 'publish'),

-- 8. Gado-Gado Jakarta (Makanan Ringan / Sehat / Vegetarian → cat 2)
(2, 2,
 'Gado-Gado Jakarta',
 'gado-gado-jakarta',
 'gado-gado-jakarta.jpg',
 'Salad sayur khas Jakarta dengan bumbu kacang kental, lontong, tahu, tempe, dan kerupuk.',
 '["2 buah tahu (goreng, potong)","100 g tempe (goreng, potong)","100 g taoge (rebus)","2 buah kentang (rebus, potong)","100 g kangkung (rebus)","2 butir telur rebus","1 buah mentimun (iris)","Kerupuk dan lontong secukupnya","Bumbu kacang: 200 g kacang tanah goreng, 5 cabai merah, 3 siung bawang putih, 2 sdm kecap manis, 1 sdm gula merah, garam, air asam, 250 ml air panas"]',
 '["Haluskan bahan bumbu kacang, masak di atas api kecil sambil diaduk hingga mengental, koreksi rasa.","Tata semua sayuran, tahu, tempe, dan telur di atas piring saji.","Siram dengan bumbu kacang panas di atasnya.","Tambahkan kerupuk, bawang goreng, dan irisan cabai rawit sesuai selera. Sajikan segera."]',
 30, 3, 'mudah', 'publish'),

-- 9. Es Teh Lemon (Minuman → cat 3)
(1, 3,
 'Es Teh Lemon',
 'es-teh-lemon',
 'es-teh-lemon.jpg',
 'Minuman teh segar dengan perasan lemon dan sedikit madu, menyegarkan di siang hari.',
 '["2 kantong teh hitam","500 ml air panas","3 sdm madu","2 buah lemon (peras airnya)","Es batu secukupnya","Irisan lemon untuk hiasan"]',
 '["Seduh teh hitam dengan air panas selama 5 menit, angkat kantong teh.","Biarkan teh sedikit dingin, tambahkan madu dan aduk hingga larut.","Masukkan perasan air lemon, aduk rata.","Tuang ke dalam gelas berisi es batu.","Hias dengan irisan lemon. Sajikan dingin."]',
 10, 2, 'mudah', 'publish'),

-- 10. Es Cendol Pandan (Minuman / Dessert → cat 3)
(2, 3,
 'Es Cendol Pandan',
 'es-cendol-pandan',
 'es-cendol-pandan.jpg',
 'Minuman dingin khas Jawa dengan cendol hijau pandan, santan gurih, dan gula merah cair yang manis legit.',
 '["Cendol: 100 g tepung beras, 1 sdm tepung tapioka, 300 ml air daun pandan, garam","Santan: 500 ml santan + 1/4 sdt garam (masak, dinginkan)","Gula merah: 200 g gula merah + 150 ml air (rebus hingga larut, saring)","Es serut atau es batu secukupnya"]',
 '["Buat cendol: campur tepung beras, tapioka, dan air pandan. Masak hingga kental. Cetak lewat saringan berlubang ke air dingin.","Rebus gula merah dengan air, saring dan dinginkan.","Masak santan dengan sedikit garam, aduk terus hingga mendidih. Dinginkan.","Siapkan gelas, masukkan es, cendol, santan, dan gula merah cair. Sajikan segera."]',
 35, 4, 'sedang', 'publish'),

-- 11. Puding Cokelat (Dessert → cat 4)
(1, 4,
 'Puding Cokelat',
 'puding-cokelat',
 'puding-cokelat.jpg',
 'Puding cokelat lembut dengan saus vla vanilla yang manis, cocok untuk camilan sore atau penutup makan.',
 '["1 bungkus agar-agar plain","150 g gula pasir","4 sdm cokelat bubuk","600 ml susu cair","200 ml air","Saus vla: 300 ml susu cair, 3 sdm gula, 1 sdt tepung maizena, 1 sdt vanila, 2 kuning telur"]',
 '["Campur agar-agar, gula, cokelat bubuk, susu, dan air dalam panci.","Masak sambil diaduk hingga mendidih. Tuang ke cetakan, dinginkan.","Buat saus: campurkan semua bahan saus, masak sambil diaduk hingga mengental.","Setelah puding set, keluarkan dari cetakan dan siram saus vla di atasnya. Sajikan dingin."]',
 45, 6, 'mudah', 'publish'),

-- 12. Klepon (Dessert / Tradisional → cat 4)
(2, 4,
 'Klepon Gula Merah',
 'klepon-gula-merah',
 'klepon-gula-merah.jpg',
 'Kue tradisional berbentuk bulat warna hijau pandan berisi gula merah, dibalut kelapa parut segar.',
 '["200 g tepung ketan","2 sdm tepung tapioka","150 ml air daun pandan (dari 10 lembar pandan blender + saring)","1/4 sdt garam","100 g gula merah (potong kecil untuk isian)","150 g kelapa parut segar + sejumput garam (kukus 5 menit)"]',
 '["Campur tepung ketan, tapioka, dan garam. Tuang air pandan sedikit demi sedikit, uleni hingga bisa dibentuk.","Ambil adonan ±20 g, pipihkan, beri 1 potong gula merah di tengah, bulatkan dan rapatkan.","Rebus dalam air mendidih hingga klepon mengapung + 3 menit. Angkat, tiriskan.","Gulingkan klepon panas ke kelapa parut hingga terbalut rata. Sajikan segera."]',
 40, 20, 'sedang', 'publish'),

-- 13. Tumis Kangkung Terasi (Sehat / Vegetarian → cat 6)
(1, 6,
 'Tumis Kangkung Terasi',
 'tumis-kangkung-terasi',
 'tumis-kangkung-terasi.jpg',
 'Sayur kangkung tumis dengan terasi dan cabai, siap saji dalam 15 menit, sederhana tapi selalu nagih.',
 '["1 ikat kangkung (petik, cuci)","3 siung bawang putih (iris)","5 bawang merah (iris)","3 cabai merah (iris serong)","2 cabai rawit (opsional)","1 sdt terasi bakar","1/2 sdt gula","Garam secukupnya","2 sdm minyak goreng"]',
 '["Panaskan minyak, tumis bawang merah dan bawang putih hingga harum.","Masukkan cabai dan terasi, aduk rata hingga terasi larut.","Masukkan kangkung, aduk dengan api besar.","Tambahkan garam dan gula, aduk cepat ±2-3 menit hingga kangkung layu namun masih hijau segar. Sajikan."]',
 15, 3, 'mudah', 'publish'),

-- 14. Ikan Bakar Jimbaran (Seafood → cat 7)
(2, 7,
 'Ikan Bakar Jimbaran',
 'ikan-bakar-jimbaran',
 'ikan-bakar-jimbaran.jpg',
 'Ikan segar khas Bali yang dimarinasi bumbu kuning rempah lalu dibakar di atas arang, disajikan dengan sambal matah.',
 '["2 ekor ikan kakap/kerapu (bersihkan)","4 sdm kecap manis","2 sdm minyak goreng","Bumbu halus: 6 bawang merah, 4 bawang putih, 4 cabai merah, 3 cm kunyit, 2 cm jahe, 2 cm lengkuas, garam","Sambal matah: 6 bawang merah iris, 5 cabai rawit iris, 2 batang serai iris, 3 sdm minyak kelapa panas, garam, jeruk limau"]',
 '["Buat sayatan pada ikan. Campurkan bumbu halus, kecap, dan minyak. Lumuri ikan, diamkan 30 menit.","Bakar ikan di atas bara arang, bolak-balik sambil dioles sisa bumbu hingga matang dan sedikit hangus di pinggirnya.","Buat sambal matah: campur semua bahan, siram dengan minyak kelapa panas, aduk rata.","Sajikan ikan bakar dengan sambal matah dan lalapan segar."]',
 55, 4, 'sedang', 'publish'),

-- 15. Tempe Mendoan (Makanan Ringan / Vegetarian → cat 2)
(1, 2,
 'Tempe Mendoan',
 'tempe-mendoan',
 'tempe-mendoan.jpg',
 'Gorengan khas Banyumas dari tempe tipis yang dibalut adonan tepung tipis-tipis lalu digoreng setengah matang, renyah di luar lembut di dalam.',
 '["300 g tempe mendoan (iris tipis lebar)","150 g tepung terigu","2 sdm tepung beras","1 batang daun bawang (iris halus)","2 lembar daun jeruk (iris halus)","3 siung bawang putih (haluskan)","1/2 sdt ketumbar bubuk","Garam & gula secukupnya","Air secukupnya","Minyak goreng","Sambal kecap: kecap + cabai rawit iris + bawang merah iris + jeruk nipis"]',
 '["Campur tepung terigu, tepung beras, bawang putih halus, daun bawang, daun jeruk, ketumbar, garam, dan gula.","Tambahkan air sedikit demi sedikit hingga adonan sedikit encer (tidak terlalu kental).","Celupkan tempe ke adonan hingga terbalut tipis merata.","Goreng dalam minyak panas dengan api sedang, jangan terlalu lama agar tetap mendoan (setengah matang). Angkat.","Sajikan dengan sambal kecap."]',
 25, 4, 'mudah', 'publish');

-- ------------------------------------------------------------
-- BOOKMARKS (sample)
-- ------------------------------------------------------------
INSERT INTO bookmarks (user_id, recipe_id) VALUES
(1, 1), (1, 2), (1, 7), (1, 9),
(2, 1), (2, 3), (2, 5),
(3, 2), (3, 4), (3, 11),
(4, 1), (4, 6), (4, 8),
(5, 3), (5, 7), (5, 12),
(6, 5), (6, 9), (6, 14),
(7, 2), (7, 10), (7, 15);

-- ------------------------------------------------------------
-- COMMENTS (sample)
-- ------------------------------------------------------------
INSERT INTO comments (user_id, recipe_id, komentar, status) VALUES
(1, 1, 'Rendangnya enak banget! Saya coba kemarin dan hasilnya persis seperti di rumah makan Padang. Terima kasih resepnya!', 'approved'),
(2, 1, 'Sudah dicoba, hasilnya mantap. Tips: tambahkan sedikit asam kandis supaya rasanya lebih otentik.', 'approved'),
(3, 2, 'Nasi goreng sederhana tapi rasanya juara. Keluarga suka semua!', 'approved'),
(4, 3, 'Soto ayamnya bening tapi gurih. Koya-nya bikin nagih. Highly recommended!', 'approved'),
(5, 7, 'Sate madura-nya mirip banget sama yang dijual di pinggir jalan. Bumbu kacangnya perfect!', 'approved'),
(1, 5, 'Rawon paling enak yang pernah saya buat sendiri di rumah. Kluweknya bikin warna hitamnya natural.', 'approved'),
(6, 11, 'Puding cokelatnya lembut banget, anak-anak suka. Vla vanilla-nya pas manisnya.', 'approved'),
(7, 14, 'Ikan bakar jimaranya beneran berasa lagi di Bali hahaha. Sambal matahnya segar!', 'approved'),
(2, 8, 'Gado-gadonya segar dan sehat. Bumbu kacangnya bisa dibuat lebih banyak buat stok.', 'approved'),
(3, 15, 'Mendoannya crispy di luar, lembut di dalam. Pas sama sambal kecap. Favorit baru!', 'approved'),
(4, 12, 'Kleponnya meledak di mulut haha, gula merahnya banyak banget. Enak!', 'approved'),
(5, 4,  'Sambal bawangnya pedasnya pas. Ayamnya empuk karena diungkep dulu. Top!', 'approved'),
(6, 6,  'Gulai kambingnya harum banget. Pas untuk lebaran kemarin!', 'approved'),
(7, 13, 'Tumis kangkung paling mudah dan cepat, cocok buat yang sibuk. Terima kasih!', 'approved'),
(1, 10, 'Es cendolnya segar dan autentik! Manisnya dari gula merah bikin khas banget.', 'pending'),
(2, 9,  'Es teh lemonnya menyegarkan, ganti gula pasir dengan madu memang lebih enak!', 'pending');

-- ------------------------------------------------------------
-- RATINGS (sample)
-- ------------------------------------------------------------
INSERT INTO ratings (user_id, recipe_id, nilai) VALUES
-- Rendang (recipe 1)
(1,1,5),(2,1,5),(3,1,4),(4,1,5),(5,1,5),(6,1,4),(7,1,5),
-- Nasi Goreng (recipe 2)
(1,2,4),(3,2,5),(4,2,4),(6,2,4),(7,2,5),
-- Soto Ayam (recipe 3)
(2,3,5),(4,3,5),(5,3,4),(7,3,4),
-- Ayam Penyet (recipe 4)
(1,4,4),(3,4,5),(5,4,4),(6,4,5),
-- Rawon (recipe 5)
(2,5,5),(4,5,5),(6,5,4),(7,5,5),
-- Gulai Kambing (recipe 6)
(1,6,4),(3,6,4),(5,6,5),
-- Sate Madura (recipe 7)
(2,7,5),(4,7,4),(6,7,5),(7,7,5),
-- Gado-Gado (recipe 8)
(1,8,4),(3,8,5),(5,8,4),
-- Es Teh Lemon (recipe 9)
(2,9,4),(4,9,4),(6,9,5),
-- Es Cendol (recipe 10)
(1,10,5),(3,10,4),(5,10,5),(7,10,4),
-- Puding Cokelat (recipe 11)
(2,11,5),(4,11,4),(6,11,5),
-- Klepon (recipe 12)
(1,12,5),(3,12,4),(5,12,5),
-- Tumis Kangkung (recipe 13)
(2,13,4),(4,13,4),(6,13,5),(7,13,4),
-- Ikan Bakar (recipe 14)
(1,14,5),(3,14,5),(5,14,4),(7,14,5),
-- Tempe Mendoan (recipe 15)
(2,15,5),(4,15,4),(6,15,5);


-- ============================================================
-- USEFUL VIEWS (opsional, memudahkan query di aplikasi)
-- ============================================================

-- v_recipe_stats : agregasi rating & bookmark per resep
CREATE OR REPLACE VIEW v_recipe_stats AS
SELECT
    r.id                                        AS recipe_id,
    r.judul,
    r.slug,
    r.status,
    r.kesulitan,
    r.waktu_memasak,
    r.porsi,
    c.nama                                      AS kategori,
    ROUND(AVG(rt.nilai), 1)                     AS avg_rating,
    COUNT(DISTINCT rt.id)                        AS total_rating,
    COUNT(DISTINCT b.id)                         AS total_bookmark,
    COUNT(DISTINCT cm.id)                        AS total_komentar
FROM recipes r
LEFT JOIN categories c  ON c.id = r.category_id
LEFT JOIN ratings  rt   ON rt.recipe_id = r.id
LEFT JOIN bookmarks b   ON b.recipe_id  = r.id
LEFT JOIN comments cm   ON cm.recipe_id = r.id AND cm.status = 'approved'
GROUP BY r.id, r.judul, r.slug, r.status, r.kesulitan,
         r.waktu_memasak, r.porsi, c.nama;

-- v_category_stats : jumlah resep publish per kategori
CREATE OR REPLACE VIEW v_category_stats AS
SELECT
    c.id,
    c.nama,
    c.slug,
    c.icon,
    COUNT(r.id) AS total_resep
FROM categories c
LEFT JOIN recipes r ON r.category_id = c.id AND r.status = 'publish'
GROUP BY c.id, c.nama, c.slug, c.icon;


-- ============================================================
-- END OF SCRIPT
-- ============================================================
