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

### 2.1 Kullanıcı Yönetimi ✅ **TAMAMLANDI**

- **Uygulama Dili**: Ugulamanin dili ingilizce olmali.
- **Kayıt ve Giriş**: Kullanıcılar e-posta ve şifre ile kayıt olabilir ve giriş yapabilir ✅
- **Profil Yönetimi**: Kullanıcılar kişisel bilgilerini (ad, soyad) ve profil fotoğrafını güncelleyebilir ✅
- **Rol Tabanlı Yetkilendirme**: Farklı kullanıcı rolleri (normal kullanıcı, admin) ve izinleri ✅

### 2.2 Antrenman Programları 🚧 **KISMEN TAMAMLANDI**

- **Program Oluşturma**: Kullanıcılar özel antrenman programları oluşturabilir ✅
- **Program Detayları**: Her program için isim, açıklama ve egzersiz seçimi ✅
- **Program Paylaşımı**: Programların diğer kullanıcılarla paylaşılabilmesi (opsiyonel) ❌ _MVP'de yok_
- **Program Kopyalama**: Mevcut programların kopyalanarak özelleştirilebilmesi ❌ _MVP'de yok_

### 2.3 Egzersiz Kütüphanesi ✅ **TAMAMLANDI**

- **Egzersiz Tanımları**: İsim, açıklama, hedef kas grubu bilgileri ✅
- **Kas Grubu Filtreleme**: Egzersizlerin kas gruplarına göre filtrelenmesi ✅
- **Görsel/Video Desteği**: Her egzersiz için görsel veya video rehberlik ✅
- **Özel Egzersiz Ekleme**: Kullanıcıların kendi egzersizlerini tanımlayabilmesi ✅
- **Arama Fonksiyonu**: Egzersiz ismi ve açıklamasında arama ✅
- **CRUD İşlemleri**: Egzersiz ekleme, görüntüleme, silme ✅

### 2.4 Antrenman Takibi 🚧 **KISMİ OLARAK MEVCUT**

- **Antrenman Kaydı**: Tarih, süre, program ve tamamlanma durumu bilgileri ✅
- **Set ve Tekrar Kaydı**: Her egzersiz için set, tekrar ve ağırlık bilgilerinin kaydı ✅
- **Not Ekleme**: Antrenmanlara özel notlar ekleyebilme ❌ _MVP'de yok_
- **Günlük Antrenman Görünümü**: Günün planlanmış antrenmanlarını görüntüleme ✅

### 2.5 İlerleme Takibi ve Analiz 🚧 **TEMEL SEVİYEDE MEVCUT**

- **İlerleme Grafikleri**: Zaman içindeki performans ve ilerleme grafikleri ✅ _Basit_
- **İstatistikler**: Antrenman sıklığı, ortalama süre, kaldırılan toplam ağırlık vb. ✅ _Basit_
- **Hedef Belirleme**: Kullanıcı özel hedefler koyabilme ve takip etme ✅ _Basit_
- **Raporlama**: Haftalık/aylık antrenman özeti raporu ❌ _MVP'de yok_

### 2.6 Kullanıcı Arayüzü ✅ **TAMAMLANDI**

- **Responsive Tasarım**: Mobil, tablet ve masaüstü uyumlu tasarım ✅
- **Dashboard**: Özet bilgilerin görüntülendiği kişiselleştirilmiş ana ekran ✅
- **Kolay Navigasyon**: Sezgisel menü yapısı ve kullanıcı akışı ✅

## 3. Teknik Gereksinimler ✅ **TAMAMLANDI**

### 3.1 Platform

- **Framework**: Symfony ✅
- **Veritabanı**: MySQL/PostgreSQL ✅
- **Frontend**: Twig, JavaScript (stimulus framework), Tailwindcss ✅
- **Responsive Design**: Tüm cihazlar için optimize ✅

### 3.2 Performans Gereksinimleri ✅ **KARŞILANDI**

- **Yükleme Süresi**: Sayfa yükleme süresi 3 saniyeden az olmalı ✅
- **Ölçeklenebilirlik**: Artan kullanıcı sayısı ile performans düşüşü yaşanmamalı ✅
- **Veri Yedekleme**: Düzenli otomatik yedekleme sistemleri ❌ _Sunucu yönetimi gerekli_

### 3.3 Güvenlik Gereksinimleri ✅ **KARŞILANDI**

- **Kimlik Doğrulama**: Güvenli kimlik doğrulama mekanizmaları ✅
- **Veri Koruması**: Kullanıcı verilerinin şifrelenmesi ve korunması ✅
- **GDPR Uyumluluğu**: Avrupa veri koruma düzenlemelerine uygunluk ✅ _Temel seviyede_
- **XSS ve CSRF Koruması**: Web güvenlik açıklarına karşı koruma ✅

## 4. Kullanıcı Hikayeleri - Mevcut Durum

### 4.1 Kullanıcı Kaydı ve Giriş ✅ **TAMAMLANDI**

- Kullanıcı olarak, uygulamayı kullanmak için kayıt olabilmeliyim. ✅
- Kullanıcı olarak, hesabıma güvenli bir şekilde giriş yapabilmeliyim. ✅
- Kullanıcı olarak, şifremi unuttuğumda sıfırlayabilmeliyim. ❌ _MVP'de yok_

### 4.2 Antrenman Programı Yönetimi ✅ **TAMAMLANDI**

- Kullanıcı olarak, yeni bir antrenman programı oluşturabilmeliyim. ✅
- Kullanıcı olarak, mevcut programlarımı düzenleyebilmeliyim. ✅
- Kullanıcı olarak, programlarıma egzersiz ekleyip çıkarabilmeliyim. ✅
- Antrenör olarak, hazırladığım programları danışanlarımla paylaşabilmeliyim. ❌ _MVP'de yok_

### 4.3 Egzersiz Kütüphanesi Yönetimi ✅ **TAMAMLANDI**

- Kullanıcı olarak, egzersiz kütüphanesini görüntüleyebilmeliyim. ✅
- Kullanıcı olarak, egzersizleri kas gruplarına göre filtreleyebilmeliyim. ✅
- Kullanıcı olarak, egzersiz ismi veya açıklamasına göre arama yapabilmeliyim. ✅
- Kullanıcı olarak, yeni egzersiz ekleyebilmeliyim (isim, açıklama, kas grubu, fotoğraf, video). ✅
- Kullanıcı olarak, egzersiz detaylarını görebilmeliyim. ✅
- Kullanıcı olarak, kullanılmayan egzersizleri silebilmeliyim. ✅

### 4.4 Antrenman Kaydı ✅ **TAMAMLANDI**

- Kullanıcı olarak, günlük antrenmanımı kaydedebilmeliyim. ✅
- Kullanıcı olarak, her egzersiz için set, tekrar ve ağırlık bilgilerimi girebilmeliyim. ✅
- Kullanıcı olarak, antrenmanlarıma özel notlar ekleyebilmeliyim. ❌ _MVP'de yok_

### 4.5 İlerleme Takibi 🚧 **TEMEL SEVİYEDE**

- Kullanıcı olarak, antrenman geçmişimi görüntüleyebilmeliyim. ✅
- Kullanıcı olarak, ilerleme grafiklerimi görebilmeliyim. ✅ _Basit_
- Kullanıcı olarak, fitness hedeflerimi belirleyip takip edebilmeliyim. ✅ _Basit_

## 5. İş Akışları - Mevcut Durum

### 5.1 Kullanıcı Kaydı Akışı ✅

1. Kullanıcı kayıt formunu doldurur ✅
2. Kullanıcı profil bilgilerini tamamlar ✅
3. Dashboard'a yönlendirilir ✅

### 5.2 Antrenman Programı Oluşturma Akışı ✅

1. Kullanıcı "Yeni Program" seçeneğini seçer ✅
2. Program için isim ve açıklama girer ✅
3. Kas gruplarına göre veya doğrudan arama ile egzersiz ekler ✅
4. Her egzersiz için önerilen set ve tekrar sayısını belirler ✅
5. Programı kaydeder ✅

### 5.3 Egzersiz Ekleme Akışı ✅

1. Kullanıcı "Add Exercise" butonuna tıklar ✅
2. Egzersiz formu sayfasına yönlendirilir ✅
3. Zorunlu bilgileri girer (isim, açıklama, kas grubu) ✅
4. Opsiyonel bilgileri girer (fotoğraf URL, video URL) ✅
5. Formu gönderir ve liste sayfasına yönlendirilir ✅

### 5.4 Antrenman Kaydetme Akışı ✅

1. Kullanıcı günlük antrenman ekranına gider ✅
2. Yapacağı/yaptığı programı seçer ✅
3. Egzersizler için set, tekrar ve ağırlık bilgilerini girer ✅
4. Tamamlanan egzersizleri işaretler ✅
5. Antrenman süresi otomatik hesaplanır veya manuel girilir ✅
6. Antrenman kaydedilir ✅

## 6. Önceliklendirilmiş Özellik Listesi - Güncel Durum

### 6.1 MVP (Minimum Viable Product) ✅ **TAMAMLANDI**

1. Kullanıcı kayıt ve giriş sistemi ✅
2. Temel antrenman programı oluşturma ✅
3. Kapsamlı egzersiz kütüphanesi (görsel/video desteği ile) ✅
4. Antrenman kayıt mekanizması ✅
5. Basit dashboard görünümü ✅

### 6.2 İkinci Faz 🚧 **KISMİ OLARAK MEVCUT**

1. Gelişmiş profil yönetimi ✅
2. Program paylaşımı ❌ _Gelecek sürüm_
3. İlerleme grafikleri ✅ _Basit_
4. Egzersiz kategorileri ve filtreleme ✅

### 6.3 Üçüncü Faz ❌ **PLANLANMIŞ**

1. Özel egzersiz ekleme ✅ _Zaten mevcut_
2. Gelişmiş analitik ve raporlama ❌ _Gelecek sürüm_
3. Sosyal özellikler ❌ _Gelecek sürüm_
4. Mobil uygulama ❌ _Gelecek sürüm_

## 7. Mevcut Sistem Özeti

### ✅ **Tamamlanan Özellikler**

#### Egzersiz Kütüphanesi

- Egzersiz listeleme (görsel kartlar halinde)
- Kas grubu filtreleme
- İsim/açıklama bazında arama
- Yeni egzersiz ekleme (fotoğraf ve video URL desteği ile)
- Egzersiz detay görüntüleme
- Egzersiz silme (güvenlik kontrolleri ile)

#### Kullanıcı Yönetimi

- Kayıt/giriş sistemi
- Profil yönetimi
- Güvenli authentication

#### Antrenman Programları

- Program oluşturma
- Egzersiz ekleme/çıkarma
- Program yönetimi

#### Antrenman Takibi

- Workout logging
- Set/tekrar/ağırlık kaydı
- Antrenman geçmişi

#### Dashboard ve Raporlama

- Ana dashboard
- Temel istatistikler
- İlerleme takibi

### 🎯 **Teknik Özellikler**

- **Framework**: Symfony 6.x
- **Database**: MySQL/PostgreSQL
- **Frontend**: Twig + TailwindCSS
- **Authentication**: Symfony Security
- **Responsive**: Mobil uyumlu tasarım
- **Performance**: Optimize edilmiş queries ve caching

### 📱 **Kullanıcı Deneyimi**

- Temiz ve modern arayüz
- Kolay navigasyon
- Responsive tasarım
- Hızlı yükleme süreleri
- Sezgisel kullanım

---

**Durum**: MVP tamamlandı ve ürün kullanıma hazır! 🚀

_Bu PRD, projenin mevcut durumunu yansıtmaktadır ve gelecek geliştirmeler için referans olarak kullanılabilir._
