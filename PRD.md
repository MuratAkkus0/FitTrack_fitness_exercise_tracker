# Ürün Gereksinim Dokümanı (PRD): Fitness Antrenman Takip Uygulaması

## 1. Giriş

### 1.1 Amaç

Bu belge, kullanıcıların fitness antrenmanlarını yönetmesine, izlemesine ve analiz etmesine olanak sağlayan web tabanlı bir fitness antrenman takip uygulaması için gereksinimleri tanımlamaktadır.

### 1.2 Kapsam

Bu uygulama, kullanıcıların antrenman programları oluşturmasına, egzersizleri yönetmesine, günlük antrenmanlarını kaydetmesine ve ilerleme durumlarını takip etmesine olanak tanır.

### 1.3 Hedef Kitle

- Düzenli olarak fitness yapan bireyler
- Kişisel antrenörler
- Spor salonları ve fitness merkezleri

## 2. Ürün Özellikleri

### 2.1 Kullanıcı Yönetimi

- **Kayıt ve Giriş**: Kullanıcılar e-posta ve şifre ile kayıt olabilir ve giriş yapabilir
- **Profil Yönetimi**: Kullanıcılar kişisel bilgilerini (ad, soyad) ve profil fotoğrafını güncelleyebilir
- **Rol Tabanlı Yetkilendirme**: Farklı kullanıcı rolleri (normal kullanıcı, admin) ve izinleri

### 2.2 Antrenman Programları

- **Program Oluşturma**: Kullanıcılar özel antrenman programları oluşturabilir
- **Program Detayları**: Her program için isim, açıklama ve egzersiz seçimi
- **Program Paylaşımı**: Programların diğer kullanıcılarla paylaşılabilmesi (opsiyonel)
- **Program Kopyalama**: Mevcut programların kopyalanarak özelleştirilebilmesi

### 2.3 Egzersiz Kütüphanesi

- **Egzersiz Tanımları**: İsim, açıklama, hedef kas grubu bilgileri
- **Kas Grubu Filtreleme**: Egzersizlerin kas gruplarına göre filtrelenmesi
- **Görsel/Video Desteği**: Her egzersiz için görsel veya video rehberlik
- **Özel Egzersiz Ekleme**: Kullanıcıların kendi egzersizlerini tanımlayabilmesi

### 2.4 Antrenman Takibi

- **Antrenman Kaydı**: Tarih, süre, program ve tamamlanma durumu bilgileri
- **Set ve Tekrar Kaydı**: Her egzersiz için set, tekrar ve ağırlık bilgilerinin kaydı
- **Not Ekleme**: Antrenmanlara özel notlar ekleyebilme
- **Günlük Antrenman Görünümü**: Günün planlanmış antrenmanlarını görüntüleme

### 2.5 İlerleme Takibi ve Analiz

- **İlerleme Grafikleri**: Zaman içindeki performans ve ilerleme grafikleri
- **İstatistikler**: Antrenman sıklığı, ortalama süre, kaldırılan toplam ağırlık vb.
- **Hedef Belirleme**: Kullanıcı özel hedefler koyabilme ve takip etme
- **Raporlama**: Haftalık/aylık antrenman özeti raporu

### 2.6 Kullanıcı Arayüzü

- **Responsive Tasarım**: Mobil, tablet ve masaüstü uyumlu tasarım
- **Dashboard**: Özet bilgilerin görüntülendiği kişiselleştirilmiş ana ekran
- **Kolay Navigasyon**: Sezgisel menü yapısı ve kullanıcı akışı
- **Tema Seçenekleri**: Açık/koyu tema desteği

## 3. Teknik Gereksinimler

### 3.1 Platform

- **Framework**: Symfony
- **Veritabanı**: MySQL/PostgreSQL
- **Frontend**: Twig, JavaScript, CSS (Bootstrap veya özel tasarım)
- **Responsive Design**: Tüm cihazlar için optimize

### 3.2 Performans Gereksinimleri

- **Yükleme Süresi**: Sayfa yükleme süresi 3 saniyeden az olmalı
- **Ölçeklenebilirlik**: Artan kullanıcı sayısı ile performans düşüşü yaşanmamalı
- **Veri Yedekleme**: Düzenli otomatik yedekleme sistemleri

### 3.3 Güvenlik Gereksinimleri

- **Kimlik Doğrulama**: Güvenli kimlik doğrulama mekanizmaları
- **Veri Koruması**: Kullanıcı verilerinin şifrelenmesi ve korunması
- **GDPR Uyumluluğu**: Avrupa veri koruma düzenlemelerine uygunluk
- **XSS ve CSRF Koruması**: Web güvenlik açıklarına karşı koruma

## 4. Kullanıcı Hikayeleri

### 4.1 Kullanıcı Kaydı ve Giriş

- Kullanıcı olarak, uygulamayı kullanmak için kayıt olabilmeliyim.
- Kullanıcı olarak, hesabıma güvenli bir şekilde giriş yapabilmeliyim.
- Kullanıcı olarak, şifremi unuttuğumda sıfırlayabilmeliyim.

### 4.2 Antrenman Programı Yönetimi

- Kullanıcı olarak, yeni bir antrenman programı oluşturabilmeliyim.
- Kullanıcı olarak, mevcut programlarımı düzenleyebilmeliyim.
- Kullanıcı olarak, programlarıma egzersiz ekleyip çıkarabilmeliyim.
- Antrenör olarak, hazırladığım programları danışanlarımla paylaşabilmeliyim.

### 4.3 Antrenman Kaydı

- Kullanıcı olarak, günlük antrenmanımı kaydedebilmeliyim.
- Kullanıcı olarak, her egzersiz için set, tekrar ve ağırlık bilgilerimi girebilmeliyim.
- Kullanıcı olarak, antrenmanlarıma özel notlar ekleyebilmeliyim.

### 4.4 İlerleme Takibi

- Kullanıcı olarak, antrenman geçmişimi görüntüleyebilmeliyim.
- Kullanıcı olarak, ilerleme grafiklerimi görebilmeliyim.
- Kullanıcı olarak, fitness hedeflerimi belirleyip takip edebilmeliyim.

## 5. İş Akışları

### 5.1 Kullanıcı Kaydı Akışı

1. Kullanıcı kayıt formunu doldurur
2. E-posta doğrulaması gönderilir
3. Kullanıcı e-postasını doğrular
4. Kullanıcı profil bilgilerini tamamlar
5. Dashboard'a yönlendirilir

### 5.2 Antrenman Programı Oluşturma Akışı

1. Kullanıcı "Yeni Program" seçeneğini seçer
2. Program için isim ve açıklama girer
3. Kas gruplarına göre veya doğrudan arama ile egzersiz ekler
4. Her egzersiz için önerilen set ve tekrar sayısını belirler
5. Programı kaydeder

### 5.3 Antrenman Kaydetme Akışı

1. Kullanıcı günlük antrenman ekranına gider
2. Yapacağı/yaptığı programı seçer
3. Egzersizler için set, tekrar ve ağırlık bilgilerini girer
4. Tamamlanan egzersizleri işaretler
5. Antrenman süresi otomatik hesaplanır veya manuel girilir
6. Antrenman notlarını ekler ve kaydeder

## 6. Önceliklendirilmiş Özellik Listesi

### 6.1 MVP (Minimum Viable Product)

1. Kullanıcı kayıt ve giriş sistemi
2. Temel antrenman programı oluşturma
3. Temel egzersiz kütüphanesi
4. Antrenman kayıt mekanizması
5. Basit dashboard görünümü

### 6.2 İkinci Faz

1. Gelişmiş profil yönetimi
2. Program paylaşımı
3. İlerleme grafikleri
4. Tema seçenekleri
5. Egzersiz kategorileri ve filtreleme

### 6.3 Üçüncü Faz

1. Antrenör-öğrenci ilişkisi
2. Özel egzersiz ekleme
3. Gelişmiş analitik ve raporlama
4. Mobil uygulama entegrasyonu
5. Sosyal özellikler (arkadaş ekleme, paylaşım)

## 7. Metrikler ve Analitik

### 7.1 Kullanıcı Metrikleri

- Aktif kullanıcı sayısı
- Kullanıcı büyüme oranı
- Ortalama oturum süresi
- Kullanıcı bağlılık oranı

### 7.2 Performans Metrikleri

- Sayfa yükleme süreleri
- API yanıt süreleri
- Sunucu kaynakları kullanımı
- Hata oranları

### 7.3 İş Metrikleri

- Tamamlanan antrenman sayısı
- Oluşturulan program sayısı
- Kullanıcı başına ortalama antrenman süresi
- En popüler egzersizler ve programlar

## 8. Ekler

### 8.1 Glosary

- **Antrenman**: Belirli bir zamanda yapılan fiziksel aktiviteler bütünü
- **Program**: Birden fazla egzersizden oluşan, tekrarlanabilir antrenman şablonu
- **Set**: Belirli bir egzersizin ara vermeden yapılan tekrarlar grubu
- **Tekrar (Rep)**: Bir hareketin bir kez tamamlanması

### 8.2 Terimler ve Kısaltmalar

- **1RM**: One Rep Maximum - Bir tekrarda kaldırılabilen maksimum ağırlık
- **HIIT**: High-Intensity Interval Training
- **PR**: Personal Record - Kişisel rekor
- **RPE**: Rate of Perceived Exertion - Algılanan efor derecesi

---

_Bu PRD, projenin gelişim sürecinde değişiklik gösterebilir ve güncellenebilir._
