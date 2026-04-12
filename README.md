# ⚖️ SmartLawyer System v1.0

> نظام الإدارة القانونية الذكي - تطوير المحامي محمد عاطف

![SmartLawyer Dashboard](https://img.shields.io/badge/Laravel-11.x-red?style=for-the-badge&logo=laravel)
![Vuexy](https://img.shields.io/badge/UI-Vuexy--Tabler-blue?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Active-success?style=for-the-badge)

---

## 📖 نظرة عامة

**SmartLawyer** هو منصة متطورة صُممت خصيصاً للمحامين لربط العمل القانوني بالبرمجة الذكية. النظام لا يكتفي بالأرشفة، بل يدير القضايا (مدني/جنائي) بنظام الخطوات الذكي (Wizard) ويقوم بحساب المواعيد القانونية آلياً.

---

## 🛠️ المميزات التقنية الذكية

- **نظام القضايا (Litigation Wizard):** تم استخدام `BS Stepper` لتقسيم إدخال القضية لخطوات، مع تبديل الحقول ديناميكياً (Dynamic Toggle) بين المدني والجنائي.
- **تتبع الجلسات (Session Tracker):** نظام يربط الجلسة بقرار المحكمة ويقوم بجدولة الجلسة القادمة في الـ Timeline فورياً.
- **تنبيهات الاستئناف:** حساب تلقائي لموعد الـ 40 يوماً في القضايا المدنية وتنبيه المستخدم قبل فوات الميعاد.
- **معالجة المستندات:** رفع ومعاينة ملفات الـ PDF والـ Contracts مباشرة باستخدام `Blob URLs` لتجاوز قيود الـ Sandbox.

---

## 🏗️ الهيكل البرمجي (Architecture)

- **Controllers:** تم فصل المنطق البرمجي (Dashboard, Contract, Litigation) لضمان سهولة التوسع.
- **Utilities:** استخدام ملف `legal-utils.js` كمركز موحد لإدارة (Flatpickr, Select2, Dropzone).
- **Validation:** استخدام `ContractRequest` و `LitigationRequest` لضمان دقة البيانات القانونية قبل دخولها للقاعدة.

---

## 🚀 التشغيل السريع (Quick Start)

1. قم بضبط إعدادات قاعدة البيانات في ملف `.env`.
2. تشغيل الأوامر التالية بالترتيب:

   ```bash
   composer install
   npm install
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```

   composer install
   npm install
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   npm run build
